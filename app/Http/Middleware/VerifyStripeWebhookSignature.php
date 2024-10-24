<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Stripe\WebhookSignature;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UnexpectedValueException;

class VerifyStripeWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // You can find your endpoint's secret in your webhook settings
        $endpoint_secret = config('services.stripe.webhook.secret');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch(UnexpectedValueException $e) {
            // Invalid payload
            return response()->json([
                'message' => 'Invalid payload',
            ], 400);
        }
        catch(SignatureVerificationException $e)
        {
            // Invalid signature
            return response()->json([
                'message' => 'Invalid signature',
            ], 403);
        }

//        try {
//            WebhookSignature::verifyHeader(
//                $request->getContent(),
//                $request->header('stripe-signature'),
//                config('services.stripe.webhook.secret'),
//                config('services.stripe.webhook.tolerance')
//            );
//        } catch (SignatureVerificationException $exception) {
//            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
//        }

        return $next($request);
    }
}
