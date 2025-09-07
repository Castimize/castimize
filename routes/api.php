<?php

use App\Http\Controllers\Api;
use App\Http\Middleware\AuthGates;
use App\Http\Middleware\RequestLogger;
use App\Http\Middleware\ValidateWcWebhookSignature;
use Illuminate\Support\Facades\Route;

// Route::get('/user', [UsersApiController::class, 'show'])->can('viewUser')->middleware('auth:sanctum');

Route::middleware(RequestLogger::class)->group(function () {
    Route::prefix('v1')->name('api.')->group(function () {
        Route::middleware('auth:sanctum', AuthGates::class)->group(function () {
            // Users
            Route::get('user', [Api\V1\UsersApiController::class, 'show'])->name('api.users.get-user');
            Route::post('users/wp', [Api\V1\UsersApiController::class, 'storeUserWp'])->name('api.users.store-user-wp');
            Route::delete('users/wp', [Api\V1\UsersApiController::class, 'deleteUserWp'])->name('api.users.delete-user-wp');

            // Customers
            Route::get('customers/wp', [Api\V1\CustomersApiController::class, 'showCustomerWp'])->name('api.customers.show-customer-wp');
            Route::get('customers/{customer}', [Api\V1\CustomersApiController::class, 'show'])->name('api.customers.show');

            // Shop owners
            Route::get('customers/{customerId}/shop-owner', [Api\V1\ShopOwnersApiController::class, 'show'])->name('api.customers.shop-owners.show');
            Route::get('customers/{customerId}/shop-owner/{shop}', [Api\V1\ShopOwnersApiController::class, 'showShop'])->name('api.customers.shop-owners.show-shop');
            Route::post('customers/{customerId}/shop-owner/{shop}/update-active', [Api\V1\ShopOwnersApiController::class, 'updateActiveShop'])->name('api.customers.shop-owners.update-active-shop');
            Route::post('customers/{customerId}/shop-owner', [Api\V1\ShopOwnersApiController::class, 'store'])->name('api.customers.shop-owners.store');
            Route::put('customers/{customerId}/shop-owner', [Api\V1\ShopOwnersApiController::class, 'update'])->name('api.customers.shop-owners.update');
            Route::put('customers/{customerId}/shop-owner/update-active', [Api\V1\ShopOwnersApiController::class, 'updateActive'])->name('api.customers.shop-owners.update-active');

            // Shop owners Payments
            Route::get('customers/{customerId}/payments/create-setup-intent', [Api\V1\PaymentsApiController::class, 'createSetupIntent'])->name('api.payments.create-setup-intent');
            Route::post('customers/{customerId}/payments/cancel-mandate', [Api\V1\PaymentsApiController::class, 'cancelMandate'])->name('api.payments.cancel-mandate');

            // Orders
            Route::post('orders/calculate-expected-delivery-date', [Api\V1\OrdersApiController::class, 'calculateExpectedDeliveryDate'])->name('api.orders.calculate-expected-delivery-date');
            Route::get('orders/wp', [Api\V1\OrdersApiController::class, 'showOrderWp'])->name('api.orders.show-order-wp');
            Route::get('orders/{order_number}', [Api\V1\OrdersApiController::class, 'show'])->name('api.orders.show');

            // Prices
            Route::post('prices/calculate', [Api\V1\PricesApiController::class, 'calculatePrice'])->name('api.prices.calculate');
            Route::post('prices/calculate/shipping', [Api\V1\PricesApiController::class, 'calculateShipping'])->name('api.prices.calculate.shipping');

            // Materials
            Route::get('materials', [Api\V1\MaterialsApiController::class, 'index'])->name('api.materials.index');
            Route::get('materials/{material}', [Api\V1\MaterialsApiController::class, 'show'])->name('api.materials.show');

            // Models
            Route::get('models/wp/{customerId}/{model}', [Api\V1\ModelsApiController::class, 'show'])->name('api.models.show');
            Route::get('models/wp/{customerId}', [Api\V1\ModelsApiController::class, 'showModelsWpCustomer'])->name('api.models.show-customer-wp-models');
            Route::post('models/wp/{customerId}/paginated', [Api\V1\ModelsApiController::class, 'showModelsWpCustomerPaginated'])->name('api.models.show-customer-wp-models-paginated');
            Route::post('models/store-from-upload', [Api\V1\ModelsApiController::class, 'storeFromUpload'])->name('api.models.store-from-upload');
            Route::post('models/wp/{customerId}', [Api\V1\ModelsApiController::class, 'store'])->name('api.models.wp.store');
            Route::post('models/wp/{customerId}/get-custom-model-name', [Api\V1\ModelsApiController::class, 'getCustomModelName'])->name('api.models.get-custom-model-name');
            Route::post('models/wp/{customerId}/get-custom-model-attributes', [Api\V1\ModelsApiController::class, 'getCustomModelAttributes'])->name('api.models.get-custom-model-attributes');
            Route::post('models/wp/{customerId}/{model}', [Api\V1\ModelsApiController::class, 'update'])->name('api.models.update');
            Route::post('models/wp/{customerId}/{model}/delete', [Api\V1\ModelsApiController::class, 'destroy'])->name('api.models.delete');

            // Shippo
            Route::post('address/validate', [Api\V1\AddressApiController::class, 'validate'])->name('api.address.validate');

            // Etsy
            Route::get('etsy/{customerId}/taxonomy', [Api\V1\EtsyApiController::class, 'getTaxonomy'])->name('api.etsy.get-taxonomy');
            Route::get('etsy/{customerId}/shop', [Api\V1\EtsyApiController::class, 'getShop'])->name('api.etsy.get-shop');
            Route::get('etsy/{customerId}/shop/authorization-url', [Api\V1\EtsyApiController::class, 'getShopAuthorizationUrl'])->name('api.etsy.get-shop-authorization-url');
            Route::get('etsy/{customerId}/shop/return-policies', [Api\V1\EtsyApiController::class, 'getShopReturnPolicies'])->name('api.etsy.get-shop-return-policies');
            Route::get('etsy/{customerId}/shop/return-policies/{returnPolicyId}', [Api\V1\EtsyApiController::class, 'getShopReturnPolicy'])->name('api.etsy.get-shop-return-policy');
            Route::post('etsy/{customerId}/shop/return-policies', [Api\V1\EtsyApiController::class, 'createShopReturnPolicy'])->name('api.etsy.create-shop-return-policy');
            Route::get('etsy/{customerId}/shop/payments/ledger-entries', [Api\V1\EtsyApiController::class, 'getShopPaymentLedgerEntries'])->name('api.etsy.get-shop-payment-ledger-entries');
            Route::get('etsy/{customerId}/shop/receipts', [Api\V1\EtsyApiController::class, 'getShopReceipts'])->name('api.etsy.get-shop-receipts');
            Route::get('etsy/{customerId}/listings', [Api\V1\EtsyApiController::class, 'getListings'])->name('api.etsy.get-listings');
            Route::get('etsy/{customerId}/listings/sync', [Api\V1\EtsyApiController::class, 'syncListings'])->name('api.etsy.sync-listings');
            Route::get('etsy/{customerId}/listings/{listingId}', [Api\V1\EtsyApiController::class, 'dgetListing'])->name('api.etsy.get-listing');
            Route::get('etsy/{customerId}/listings/{listingId}/inventory', [Api\V1\EtsyApiController::class, 'getListingInventory'])->name('api.etsy.get-listing-inventory');
            Route::get('etsy/{customerId}/listings/{listingId}/properties', [Api\V1\EtsyApiController::class, 'getListingProperties'])->name('api.etsy.get-listing-properties');
            Route::get('etsy/{customerId}/listings/{listingId}/delete', [Api\V1\EtsyApiController::class, 'deleteListing'])->name('api.etsy.delete-listing');
            Route::get('etsy/{customerId}/shipping-carriers', [Api\V1\EtsyApiController::class, 'getShippingCarriers'])->name('api.etsy.get-shipping-carriers');
            Route::get('etsy/{customerId}/shipping-profile', [Api\V1\EtsyApiController::class, 'getShippingProfile'])->name('api.etsy.get-shipping-profile');
            Route::post('etsy/{customerId}/shipping-profile', [Api\V1\EtsyApiController::class, 'createShippingProfile'])->name('api.etsy.create-shipping-profile');
        });

        // Woocommerce endpoints
        Route::middleware(ValidateWcWebhookSignature::class)
            ->post('customers/wp', [Api\V1\CustomersApiController::class, 'storeCustomerWp'])->name('api.customers.store-customer-wp');
        Route::middleware(ValidateWcWebhookSignature::class)
            ->post('customers/wp/update', [Api\V1\CustomersApiController::class, 'updateCustomerWp'])->name('api.customers.update-customer-wp');
        Route::middleware(ValidateWcWebhookSignature::class)
            ->delete('customers/wp', [Api\V1\CustomersApiController::class, 'deleteCustomerWp'])->name('api.customers.delete-customer.wp');

        Route::middleware(ValidateWcWebhookSignature::class)
            ->post('orders/wp', [Api\V1\OrdersApiController::class, 'storeOrderWp'])->name('api.orders.store-order-wp');

        Route::middleware(ValidateWcWebhookSignature::class)
            ->post('orders/wp/update', [Api\V1\OrdersApiController::class, 'updateOrderWp'])->name('api.orders.store-order-wp-update');
    });
});
