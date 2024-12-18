<?php

use App\Http\Controllers\ModelsDownloadController;
use App\Http\Controllers\PoLabelsDownloadController;
use App\Http\Controllers\PrivateRejectionImageController;
use App\Http\Controllers\Webhooks\Payments\StripeWebhookController;
use App\Http\Controllers\Webhooks\Shipping\ShippoWebhookController;
use App\Http\Middleware\RequestLogger;
use App\Http\Middleware\VerifyShippoWebhookSignature;
use App\Http\Middleware\VerifyStripeWebhookSignature;
use App\Services\Exact\ExactOnlineService;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'exact'], function () {
    Route::get('connect', ['as' => 'exact.connect', 'uses' => 'ExactOnlineController@appConnect']);
    Route::post('authorize', ['as' => 'exact.authorize', 'uses' => 'ExactOnlineController@appAuthorize']);
    Route::get('oauth', ['as' => 'exact.callback', 'uses' => 'ExactOnlineController@appCallback']);
    Route::post('callback-webhook', ['as' => 'exact.webhook', 'uses' => 'ExactOnlineController@appCallbackWebhook']);

    Route::get('test', function () {
        dd((new ExactOnlineService())->getGlAccounts());
    });
});

Route::group(['middleware' => [RequestLogger::class]], function () {
    Route::group([
        'namespace' => 'App\Http\Controllers',
    ], function () {
        Route::post('/webhooks/payment/stripe/callback', StripeWebhookController::class)
            ->name('webhooks.payment.stripe.callback')
            ->middleware(VerifyStripeWebhookSignature::class);
        Route::post('/webhooks/shipping/shippo/callback', ShippoWebhookController::class)
            ->name('webhooks.shipping.shippo.callback')
            ->middleware(VerifyShippoWebhookSignature::class);
    });
});

Route::get('/models/download', ModelsDownloadController::class)
    ->name('models.download');
Route::get('/po/labels/download', PoLabelsDownloadController::class)
    ->name('po.labels.download');
Route::get('/images/rejections/{id}', PrivateRejectionImageController::class)
    ->name('images.rejections');
