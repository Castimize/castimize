<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        Log::info($request->header());
        Log::info(print_r($request->all(), true));
        $signatureWc = $request->header('x-wc-webhook-signature');
        $signatureWp = $request->header('x-wp-webhook-signature');
        if (empty($signatureWc) && empty($signatureWp)) {
            return response(['Invalid key'], 401);
        }

        $payload = $request->getContent();
        $calculated_hmac = base64_encode(hash_hmac('sha256', $payload, env('WOOCOMMERCE_KEY'), true));

        if ($signatureWc != $calculated_hmac && $signatureWp != $calculated_hmac) {
            Log::info('Invalid payload');
            return response(['Invalid payload'], 401);
        }

        return $next($request);
    }
}
