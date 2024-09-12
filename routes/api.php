<?php

use App\Http\Middleware\AuthGates;
use Illuminate\Support\Facades\Route;

//Route::get('/user', [UsersApiController::class, 'show'])->can('viewUser')->middleware('auth:sanctum');


Route::group([
    'prefix' => 'v1',
    'as' => 'api.',
    'namespace' => 'App\Http\Controllers\Api\V1',
    'middleware' => ['auth:sanctum', AuthGates::class]
], function () {
    Route::get('user', 'UsersApiController@show');
});
