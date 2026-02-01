<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function __construct() {}

    /**
     * Handle successful calls on the controller.
     */
    protected function successMethod(array $parameters = []): Response
    {
        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle calls to missing methods on the controller.
     */
    protected function missingMethod(array $parameters = []): Response
    {
        return new Response;
    }

    /**
     * Handle calls to invalid payload on the controller.
     */
    protected function badRequestMethod(): Response
    {
        return new Response('', Response::HTTP_BAD_REQUEST);
    }
}
