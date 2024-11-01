<?php

use Castimize\InlineTextEdit\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/

Route::name('nova.api.inline-text-edit.')
    ->controller(ApiController::class)
    ->group(function () {
        Route::post('update', 'update')
            ->name('update');
    });
