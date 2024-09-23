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
    Route::delete('customers/wp', 'CustomersApiController@deleteCustomerWp')->name('api.customers.delete-customer.wp');

    //Orders
    Route::get('orders/wp', 'OrdersApiController@showOrderWp')->name('api.orders.show-order-wp');
    Route::get('orders/{order}', 'OrdersApiController@show')->name('api.orders.show');
    Route::post('orders/wp', 'OrdersApiController@storeOrderWp')->name('api.orders.store-order-wp');
    Route::post('orders/wp/stripe-callback', 'OrdersApiCOntroller@orderPaidCallback')->name('api.orders.wp.stripe-callback');

    // Prices
    Route::post('prices/calculate', 'PricesApiController@calculatePrice')->name('api.prices.calculate');

    // Models
    Route::post('models/store-from-upload', 'ModelsApiController@storeFromUpload')->name('api.models.store-from-upload');
});
