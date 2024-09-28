<?php

use App\Http\Middleware\AuthGates;
use App\Http\Middleware\ValidateWcWebhookSignature;
use Illuminate\Support\Facades\Route;

//Route::get('/user', [UsersApiController::class, 'show'])->can('viewUser')->middleware('auth:sanctum');


Route::group([
    'prefix' => 'v1',
    'as' => 'api.',
    'namespace' => 'App\Http\Controllers\Api\V1',
], function () {
    Route::group([
        'middleware' => ['auth:sanctum', AuthGates::class],
    ], function () {
        // Users
        Route::get('user', 'UsersApiController@show');
        Route::post('users/wp', 'UsersApiController@storeUserWp')->name('api.users.store-user-wp');
        Route::delete('users/wp', 'UsersApiController@deleteUserWp')->name('api.users.delete-user-wp');

        // Customers
        Route::get('customers/wp', 'CustomersApiController@showCustomerWp')->name('api.customers.show-customer-wp');
        Route::get('customers/{customer}', 'CustomersApiController@show')->name('api.customers.show');

        //Orders
        Route::post('orders/calculate-expected-delivery-date', 'OrdersApiController@calculateExpectedDeliveryDate')->name('api.orders.calculate-expected-delivery-date');
        Route::get('orders/wp', 'OrdersApiController@showOrderWp')->name('api.orders.show-order-wp');
        Route::get('orders/{order}', 'OrdersApiController@show')->name('api.orders.show');

        // Prices
        Route::post('prices/calculate', 'PricesApiController@calculatePrice')->name('api.prices.calculate');

        // Models
        Route::get('models/wp/{customerId}', 'ModelsApiController@showModelsWpCustomer')->name('api.models.show-customer-wp-models');
        Route::post('models/store-from-upload', 'ModelsApiController@storeFromUpload')->name('api.models.store-from-upload');
    });

    // Woocommerce endpoints
    Route::post('customers/wp', 'CustomersApiController@storeCustomerWp')->name('api.customers.store-customer-wp');
    Route::post('customers/wp/update', 'CustomersApiController@updateCustomerWp')->name('api.customers.update-customer-wp');
    Route::middleware(ValidateWcWebhookSignature::class)
        ->delete('customers/wp', 'CustomersApiController@deleteCustomerWp')->name('api.customers.delete-customer.wp');

    Route::middleware(ValidateWcWebhookSignature::class)
        ->post('orders/wp', 'OrdersApiController@storeOrderWp')->name('api.orders.store-order-wp');
});
