<?php

namespace App\Http\Middleware;

use App\Services\Admin\LogRequestService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateWcWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $signature = $request->header('x-wc-webhook-signature');
        if (empty($signature)) {
            LogRequestService::addResponse($request, [
                'message' => 'Invalid key',
            ], 401);
            return response(['Invalid key'], 401);
        }

        $payload = $request->getContent();
        $calculated_hmac = base64_encode(hash_hmac('sha256', $payload, env('WOOCOMMERCE_KEY'), true));

        if ($signature != $calculated_hmac) {
            LogRequestService::addResponse($request, [
                'message' => 'Invalid payload',
            ], 401);
            return response(['Invalid payload'], 401);
        }

        return $next($request);
    }
}
