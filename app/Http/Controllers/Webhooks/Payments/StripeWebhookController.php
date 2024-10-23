<?php

namespace App\Http\Controllers\Webhooks\Payments;

use App\Http\Controllers\Webhooks\WebhookController;
use App\Jobs\UploadToOrderQueue;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JsonException;
use Stripe\Event;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Response;
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
        }
        return $this->successMethod();
    }
}
