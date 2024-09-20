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
    // Users
    Route::get('user', 'UsersApiController@show');
    Route::post('users/wp', 'UsersApiController@storeUserWp')->name('api.users.store-user-wp');
    Route::delete('users/wp', 'UsersApiController@deleteUserWp')->name('api.users.delete-user-wp');

    // Customers
    Route::get('customers/wp', 'CustomersApiController@showCustomerWp')->name('api.customers.show-customer-wp');
    Route::get('customers/{customer}', 'CustomersApiController@show')->name('api.customers.show');
    Route::post('customers/wp', 'CustomersApiController@storeCustomerWp')->name('api.customers.store-customer-wp');
    Route::delete('customers/wp', 'CustomersApiController@deleteCustomerWp')->name('api.customers.delete-custoer.wp');

    // Prices
    Route::post('prices/calculate', 'PricesApiController@calculatePrice')->name('api.prices.calculate');
});
