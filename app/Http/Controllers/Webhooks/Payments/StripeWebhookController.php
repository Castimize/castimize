<?php

namespace App\Http\Controllers\Webhooks\Payments;

use App\Http\Controllers\Webhooks\WebhookController;
use App\Jobs\UploadToOrderQueue;
use App\Models\Order;
use App\Services\Admin\LogRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JsonException;
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
        $order = Order::with(['uploads'])
            ->where('order_number', $paymentIntent->metadata->order_id)
            ->first();

        if ($order !== null) {
            $order->status = 'processing';
            $order->is_paid = true;
            $order->paid_at = Carbon::createFromTimestamp($paymentIntent->created, 'GMT')?->setTimezone(env('APP_TIMEZONE'));
            $order->save();

            foreach ($order->uploads as $upload) {
                // Set upload to order queue
                UploadToOrderQueue::dispatch($upload);
            }

            try {
                LogRequestService::addResponse(request(), $order);
            } catch (Throwable $exception) {
                Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            }
        }

        return $this->successMethod();
    }

    /**
     * @param PaymentIntent $paymentIntent
     * @return Response
     */
    protected function handlePaymentIntentCanceled(PaymentIntent $paymentIntent): Response
    {
        $order = Order::with(['uploads'])
            ->where('order_number', $paymentIntent->metadata->order_id)
            ->first();

        if ($order !== null) {
            $order->status = 'canceled';
            $order->save();
            $order->delete();

            try {
                LogRequestService::addResponse(request(), $order);
            } catch (Throwable $exception) {
                Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            }
        }
        return $this->successMethod();
    }
}
