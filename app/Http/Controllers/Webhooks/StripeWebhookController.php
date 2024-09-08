<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    /**
     * StripeWebhookController constructor.
     */
    public function __construct()
    {
    }

    /**
     * Handle a Stripe webhook call.
     *
     * @param Request $request
     * @return Response
     * @throws InvalidChannelException
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent());

//        $handledEvent = $this->getHandledEvent($payload->id);
//        if ($handledEvent !== null && $handledEvent->state === HandledEvent::STATE_SUCCEEDED) {
//            return $this->successMethod();
//        }
//
//        $method = 'handle' . Str::studly(str_replace('.', '_', $payload->type));
//
//        if (method_exists($this, $method)) {
//            if ($handledEvent !== null) {
//                $handledEvent->state = HandledEvent::STATE_SUCCEEDED;
//                $this->saveHandledEvent($handledEvent);
//            } else {
//                $this->storeHandledEvent(
//                    $payload->id,
//                    $payload->type,
//                    Carbon::createFromTimestamp($payload->created),
//                    HandledEvent::STATE_SUCCEEDED
//                );
//            }
//            return $this->{$method}($payload);
//        }

        return $this->missingMethod();
    }

    /**
     * @param $payload
     * @return Response
     */
    protected function handlePaymentIntentSucceeded($payload): Response
    {
        $intent = $payload->data->object;
        $orderId = $intent->charges->data[0]->metadata->order_id;
        $identifier = $intent->charges->data[0]->metadata->identifier ?? '';
        //event(new OrderPaymentSucceeded($orderId, $identifier, $payload));
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

    /**
     * @param $payload
     * @return Response
     * @throws InvalidChannelException
     */
    protected function handlePaymentIntentPaymentFailed($payload): Response
    {
        $intent = $payload->data->object;
        $errorMessage = $intent->last_payment_error->message ?? '';
        //Notifier::notify($errorMessage, Notifier::JOBS_CHANNEL);
//        event(new StripeOrderPaymentCanceled($orderId));
        return $this->successMethod();
    }

    /**
     * @param $payload
     * @return Response
     */
    protected function handleBalanceAvailable($payload): Response
    {
        $balance = $payload->data->object;
        //CreateStripeTransferFromSupplierTransaction::dispatch($balance, $payload)->onQueue('jobs');
        return $this->successMethod();
    }

    ////////// Handle Connect webhook endpoints /////////////

    /**
     * @param $payload
     * @return Response
     */
    protected function handleConnectBalanceAvailable($payload): Response
    {
//        $balance = $payload->data->object;
//        $account = $payload->account;
//        CreateStripePayoutFromSupplierTransaction::dispatch($balance, $account)->onQueue('jobs');

        return $this->successMethod();
    }

    /**
     * Handle successful calls on the controller.
     *
     * @param array $parameters
     * @return Response
     */
    protected function successMethod(array $parameters = []): Response
    {
        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param array $parameters
     * @return Response
     */
    protected function missingMethod(array $parameters = []): Response
    {
        return new Response;
    }
}
