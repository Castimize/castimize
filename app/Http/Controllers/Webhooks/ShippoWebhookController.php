<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ShippoWebhookController extends Controller
{
    /**
     * ShippoWebhookController constructor.
     */
    public function __construct()
    {
    }

    /**
     * Handle a Shippo webhook call.
     *
     * @param Request $request
     * @return Response
     */
    public function handleWebhook(Request $request): Response
    {
        Log::info(print_r($request->all(), true));

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
}
