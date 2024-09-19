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

    // Customers
    Route::get('customers/wp', 'CustomersApiController@showCustomerWp')->name('api.customers.show-customer-wp');
    Route::get('customers/{customer}', 'CustomersApiController@show')->name('api.customers.show');
    Route::post('customers', 'CustomersApiController@store')->name('api.customers.store');

    // Prices
    Route::post('prices/calculate', 'PricesApiController@calculatePrice')->name('api.prices.calculate');
});
