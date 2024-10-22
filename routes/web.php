<?php

use App\Http\Middleware\RequestLogger;
use App\Http\Middleware\VerifyShippoWebhookSignature;
use App\Http\Middleware\VerifyStripeWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => [RequestLogger::class]], function () {
    Route::group([
        'namespace' => 'App\Http\Controllers',
    ], function () {
        Route::post('/webhooks/payment/stripe/callback', 'Webhooks\Payments\StripeWebhookController@handleWebhook')
            ->name('webhooks.payment.stripe.callback')
            ->middleware(VerifyStripeWebhookSignature::class);
        Route::post('/webhooks/shipping/shippo/callback', 'Webhooks\Shipping\ShippoWebhookController@handleWebhook')
            ->name('webhooks.shipping.shippo.callback')
            ->middleware(VerifyShippoWebhookSignature::class);
    });
});
