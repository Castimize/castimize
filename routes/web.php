<?php

use App\Http\Middleware\VerifyShippoWebhookSignature;
use App\Http\Middleware\VerifyStripeWebhookSignature;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
    //return view('welcome');
//});

Route::group([
    'namespace' => 'App\Http\Controllers',
], function () {
    Route::post('/webhooks/payment/stripe/callback', 'Webhooks\StripeWebhookController@handleWebhook')
        ->name('webhooks.payment.stripe.callback')
        ->middleware(VerifyStripeWebhookSignature::class);
    Route::post('/webhooks/shipping/shippo/callback', 'Webhooks\ShippoWebhookController@handleWebhook')
        ->name('webhooks.shipping.shippo.callback')
        ->middleware(VerifyShippoWebhookSignature::class);
});
