<?php

namespace App\Http\Controllers\Webhooks\Payments;

use App\Http\Controllers\Webhooks\WebhookController;
use App\Jobs\CreateInvoicesFromOrder;
use App\Jobs\SetOrderCanceled;
use App\Jobs\SetOrderPaid;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\OrdersService;
use App\Services\Admin\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use JsonException;
use Stripe\Charge;
use Stripe\Customer as StripeCustomer;
use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use UnexpectedValueException;

class StripeWebhookController extends WebhookController
{
    public function __construct(
        private OrdersService $ordersService,
        private PaymentService $paymentService,
    ) {
        parent::__construct();
    }

    /**
     * Handle a Stripe webhook call.
     *
     * @throws JsonException
     */
    public function __invoke(Request $request): Response
    {
        try {
            $event = Event::constructFrom(
                json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)
            );
        } catch(UnexpectedValueException $e) {
            LogRequestService::addResponse($request, [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e->getCode());
            // Invalid payload
            return $this->badRequestMethod();
        }

        // Handle the event
        switch ($event->type) {
            case 'customer.created':
            case 'customer.updated':
                $customer = $event->data->object; // contains a \Stripe\Customer
                $this->handleCustomerCreatedAndUpdated($customer);
                break;
            case 'payment_intent.succeeded':
                /**
                 * @var $paymentIntent PaymentIntent
                 */
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                // Then define and call a method to handle the successful payment intent.
                $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'payment_intent.canceled':
                /**
                 * @var $paymentIntent PaymentIntent
                 */
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                // Then define and call a method to handle the successful payment intent.
                $this->handlePaymentIntentCanceled($paymentIntent);
                break;
            case 'setup_intent.created':
                /**
                 * @var $setupIntent SetupIntent
                 */
                $setupIntent = $event->data->object; // contains a \Stripe\SetupIntent
                $this->handleSetupIntentCreated($setupIntent);
                break;
            case 'setup_intent.succeeded':
                /**
                 * @var $setupIntent SetupIntent
                 */
                $setupIntent = $event->data->object; // contains a \Stripe\SetupIntent
                $this->handleSetupIntentSucceeded($setupIntent);
                break;
            case 'setup_intent.canceled':
            case 'setup_intent.requires_action':
            case 'setup_intent.setup_failed':
                Log::info('Event: ' . $event->type . ': ' . PHP_EOL . print_r($event->data, true));
                return $this->successMethod();
            case 'charge.refunded':
                $charge = $event->data->object; // contains a \Stripe\Charge
                $this->handleChargeRefunded($charge);
                break;
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return $this->missingMethod();
    }

    protected function handleCustomerCreatedAndUpdated(StripeCustomer $stripeCustomer): Response
    {
        $customer = Customer::where('email', $stripeCustomer->email)
            ->first();

        if ($customer) {
            $stripeData = $customer->stripe_data ?? [];
            if (! array_key_exists('stripe_id', $stripeData)) {
                $stripeData['stripe_id'] = $stripeCustomer->id;
                $customer->stripe_data = $stripeData;
                $customer->save();
            }

            try {
                LogRequestService::addResponse(request(), $customer);
            } catch (Throwable $exception) {
                Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            }
        }

        return $this->successMethod();
    }

    protected function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent): Response
    {
        $logRequestId = null;
        if (request()->has('log_request_id')) {
            $logRequestId = request()->log_request_id;
        }

        Bus::chain([
            new SetOrderPaid($paymentIntent, $logRequestId),
            new CreateInvoicesFromOrder($paymentIntent->metadata->order_id),
        ])
            ->onQueue('stripe')
            ->dispatch();

        return $this->successMethod();
    }

    protected function handlePaymentIntentCanceled(PaymentIntent $paymentIntent): Response
    {
        $logRequestId = null;
        if (request()->has('log_request_id')) {
            $logRequestId = request()->log_request_id;
        }

        SetOrderCanceled::dispatch($paymentIntent, $logRequestId)
            ->onQueue('stripe')
            ->delay(now()->addMinute());

        return $this->successMethod();
    }

    protected function handleSetupIntentCreated(SetupIntent $setupIntent): Response
    {
        try {
            $customerId = $setupIntent->metadata->customer_id ?? null;
            if ($customerId) {
                $customer = Customer::find($customerId);
            } else {
                $customer = Customer::whereJsonContains('stripe_data->stripe_id', $setupIntent->customer)->first();
            }

            LogRequestService::addResponse(request(), $setupIntent);

            return $this->successMethod();
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());

            return $this->badRequestMethod();
        }
    }

    protected function handleSetupIntentSucceeded(SetupIntent $setupIntent): Response
    {
        $customerId = $setupIntent->metadata->customer_id ?? null;
        if ($customerId) {
            $customer = Customer::find($customerId);
        } else {
            $customer = Customer::whereJsonContains('stripe_data->stripe_id', $setupIntent->customer)->first();
        }
        if ($customer) {
            $paymentMethod = $this->paymentService->getStripePaymentMethod($setupIntent->payment_method);
            $paymentMethod?->attach([
                'customer' => $customer->stripe_data['stripe_id'],
            ]);

            $stripeData = $customer->stripe_data ?? [];
            $stripeData['mandate_id'] = $setupIntent->mandate;
            $stripeData['payment_method'] = $setupIntent->payment_method;
            $stripeData['payment_method_chargable'] = true;
            $stripeData['payment_method_accepted_at'] = now()->timestamp;
            $customer->stripe_data = $stripeData;
            $customer->save();
        }

        try {
            LogRequestService::addResponse(request(), $setupIntent);

            return $this->successMethod();
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());

            return $this->badRequestMethod();
        }
    }

    protected function handleChargeRefunded(Charge $charge): Response
    {
        $order = Order::with(['uploads'])
            ->where('order_number', $charge->metadata->order_id)
            ->first();

        $this->ordersService->handleStripeRefund(
            order: $order,
            charge: $charge,
        );
        try {
            LogRequestService::addResponse(request(), $order);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return $this->successMethod();
    }
}
