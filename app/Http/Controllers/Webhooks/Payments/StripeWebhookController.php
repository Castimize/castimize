<?php

namespace App\Http\Controllers\Webhooks\Payments;

use App\Http\Controllers\Webhooks\WebhookController;
use Illuminate\Http\Request;
use JsonException;
use Stripe\Event;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class StripeWebhookController extends WebhookController
{
    /**
     * StripeWebhookController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle a Stripe webhook call.
     *
     * @param Request $request
     * @return Response
     * @throws JsonException
     */
    public function handleWebhook(Request $request): Response
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
        // For now already handled by woocommerce and order is already paid
//        $order = Order::where('order_number', $paymentIntent->metadata->order_id)->first();
//        if ($order !== null) {
//            $order->is_paid = true;
//            $order->paid_at = Carbon::createFromTimestamp($paymentIntent->created, 'GMT')?->setTimezone(env('APP_TIMEZONE'));
//            $order->save();
//            return $this->successMethod();
//        }

//        Log::info('Order not found, payment intent' . print_r($paymentIntent->toArray(), true));
        return $this->successMethod();
    }

    /**
     * @param $payload
     * @return Response
     */
    protected function handlePaymentIntentCanceled($payload): Response
    {
        $intent = $payload->data->object;
        $orderId = $intent->charges->data[0]->metadata->order_id;
        //event(new OrderPaymentCanceled($orderId, $payload));
        return $this->successMethod();
    }
}
