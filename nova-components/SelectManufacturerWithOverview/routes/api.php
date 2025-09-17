<?php

use Castimize\SelectManufacturerWithOverview\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Select manufacturer with overview API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/

Route::name('nova.api.select-manufacturer-with-overview.')
    ->controller(ApiController::class)
    ->group(function () {
        Route::post('get-overview-item', 'getOverviewItem')
            ->name('get-overview-item');
        Route::post('get-overview-footer', 'getOverviewFooter')
            ->name('get-overview-footer');
    });
