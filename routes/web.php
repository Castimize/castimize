<?php

use App\Http\Middleware\VerifyStripeWebhookSignature;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
    //return view('welcome');
//});

Route::group([
    'namespace' => 'App\Http\Controllers',
], function () {
    Route::post('/payment/providers/stripe/callback', 'Webhooks\StripeWebhookController@handleWebhook')
        ->middleware(VerifyStripeWebhookSignature::class);
});
