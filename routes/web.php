<?php

use App\Http\Controllers\ExactOnlineController;
use App\Http\Controllers\ModelsDownloadController;
use App\Http\Controllers\PoLabelsDownloadController;
use App\Http\Controllers\PrivateRejectionImageController;
use App\Http\Controllers\Webhooks\Etsy\EtsyAuthController;
use App\Http\Controllers\Webhooks\Payments\StripeWebhookController;
use App\Http\Controllers\Webhooks\Shipping\ShippoWebhookController;
use App\Http\Middleware\RequestLogger;
use App\Http\Middleware\VerifyShippoWebhookSignature;
use App\Http\Middleware\VerifyStripeWebhookSignature;
use App\Services\Exact\ExactOnlineService;
use Illuminate\Support\Facades\Route;

Route::prefix('exact')->group(function () {
    Route::get('connect', [ExactOnlineController::class, 'appConnect'])->name('exact.connect');
    Route::post('authorize', [ExactOnlineController::class, 'appAuthorize'])->name('exact.authorize');
    Route::get('oauth', [ExactOnlineController::class, 'appCallback'])->name('exact.callback');
    Route::post('callback-webhook', [ExactOnlineController::class, 'appCallbackWebhook'])->name('exact.webhook');

    Route::get('test', function () {
        dd((new ExactOnlineService)->getGlAccounts());
    });
});

Route::middleware(RequestLogger::class)->group(function () {
    Route::post('/webhooks/payment/stripe/callback', StripeWebhookController::class)
        ->name('webhooks.payment.stripe.callback')
        ->middleware(VerifyStripeWebhookSignature::class);
    Route::post('/webhooks/shipping/shippo/callback', ShippoWebhookController::class)
        ->name('webhooks.shipping.shippo.callback')
        ->middleware(VerifyShippoWebhookSignature::class);

    Route::prefix('providers')->group(function () {
        Route::get('etsy/oauth', EtsyAuthController::class)
            ->name('providers.etsy.oauth');
    });
});

Route::get('/models/download', ModelsDownloadController::class)
    ->name('models.download');
Route::get('/po/labels/download', PoLabelsDownloadController::class)
    ->name('po.labels.download');
Route::get('/images/rejections/{id}', PrivateRejectionImageController::class)
    ->name('images.rejections');
