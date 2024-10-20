<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Castimize\SelectWithOverview\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/
Route::name('nova.api.select-with-overview.')
    ->controller(ApiController::class)
    ->group(function () {
        Route::post('get-overview-item', 'getOverviewItem')
            ->name('get-overview-item');
        Route::post('get-overview-footer', 'getOverviewFooter')
            ->name('get-overview-footer');
    });
