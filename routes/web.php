<?php

use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
    //return view('welcome');
//});


Route::post('/payment/providers/stripe/callback', 'App\Http\Controllers\Webhooks\StripeWebhookController@handleWebhook')
    ->withoutMiddleware(VerifyWebhookSignature::class);
