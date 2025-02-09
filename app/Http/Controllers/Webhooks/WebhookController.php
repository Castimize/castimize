<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JsonException;
use Stripe\Event;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class WebhookController extends Controller
{
    /**
     * WebhookController constructor.
     */
    public function __construct()
    {
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

    /**
     * Handle calls to invalid payload on the controller.
     *
     * @return Response
     */
    protected function invalidMethod(): Response
    {
        return new Response('', Response::HTTP_BAD_REQUEST);
    }
}
