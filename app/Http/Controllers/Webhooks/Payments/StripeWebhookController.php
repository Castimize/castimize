<?php

namespace App\Http\Controllers\Webhooks\Payments;

use App\Http\Controllers\Webhooks\WebhookController;
use App\Jobs\SetOrderCanceled;
use App\Jobs\SetOrderPaid;
use App\Jobs\UploadToOrderQueue;
use App\Models\Order;
use App\Services\Admin\LogRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JsonException;
use Stripe\Charge;
use Stripe\Event;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use UnexpectedValueException;

class StripeWebhookController extends WebhookController
{
    /**
     * Handle a Stripe webhook call.
     *
     * @param Request $request
     * @return Response
     * @throws JsonException
     */
    public function __invoke(Request $request): Response
    {
        try {
            $event = Event::constructFrom(
                json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)
            );
        } catch(UnexpectedValueException $e) {
            LogRequestService::addResponse($request, ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], $e->getCode());
            // Invalid payload
            return $this->invalidMethod();
        }

        // Handle the event
        switch ($event->type) {
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
            case 'charge.refunded':
                $charge = $event->data->object; // contains a \Stripe\Charge
                break;
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return $this->missingMethod();
    }

    /**
     * @param PaymentIntent $paymentIntent
     * @return Response
     */
    protected function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent): Response
    {
        $logRequestId = null;
        if (request()->has('log_request_id')) {
            $logRequestId = request()->log_request_id;
        }

        SetOrderPaid::dispatch($paymentIntent, $logRequestId)
            ->onQueue('stripe')->delay(now()->addMinute());

        return $this->successMethod();
    }

    /**
     * @param PaymentIntent $paymentIntent
     * @return Response
     */
    protected function handlePaymentIntentCanceled(PaymentIntent $paymentIntent): Response
    {
        $logRequestId = null;
        if (request()->has('log_request_id')) {
            $logRequestId = request()->log_request_id;
        }

        SetOrderCanceled::dispatch($paymentIntent, $logRequestId)
            ->onQueue('stripe')->delay(now()->addMinute());

        return $this->successMethod();
    }

    protected function handleChargeRefunded(Charge $charge): Response
    {
        $order = Order::with(['uploads'])
            ->where('order_number', $charge->metadata->order_id)
            ->first();

        if ($order !== null && $charge->status === 'succeeded' && $charge->refunded) {
            $order->total_refund = $charge->amount_refunded;
            if ($order->total === $order->total_refund) {
                $order->total_refund_tax = $order->total_tax;
            }
            $order->save();

            try {
                LogRequestService::addResponse(request(), $order);
            } catch (Throwable $exception) {
                Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            }
        }

        return $this->successMethod();
    }
}
