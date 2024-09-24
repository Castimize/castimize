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
        $signature = $request->header('x-wp-webhook-signature');
        if (empty($signature)) {
            return response(['Invalid key'], 401);
        }

        $payload = $request->getContent();
        $calculated_hmac = base64_encode(hash_hmac('sha256', $payload, env('WOOCOMMERCE_KEY'), true));

        if ($signature != $calculated_hmac) {
            return response(['Invalid key'], 401);
        }



        return $next($request);
    }
}
