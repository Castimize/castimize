<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Order;
use App\Models\Upload;
use App\Models\User;

trait NeedsOrderWithUpload
{
    protected ?User $systemUser = null;

    protected function getSystemUser(): User
    {
        if ($this->systemUser === null) {
            $this->systemUser = User::find(1) ?? User::factory()->create(['id' => 1]);
        }

        return $this->systemUser;
    }

    protected function createTestCustomer(array $overrides = []): Customer
    {
        return Customer::factory()->create(array_merge([
            'wp_id' => fake()->numberBetween(100, 999),
        ], $overrides));
    }

    protected function createTestOrder(
        ?Customer $customer = null,
        ?Currency $currency = null,
        ?Country $country = null,
        array $overrides = []
    ): Order {
        $systemUser = $this->getSystemUser();
        $customer = $customer ?? $this->createTestCustomer();
        $currency = $currency ?? Currency::first();
        $country = $country ?? Country::first();

        return Order::factory()->create(array_merge([
            'wp_id' => fake()->numberBetween(1000, 9999),
            'customer_id' => $customer->id,
            'currency_id' => $currency?->id,
            'country_id' => $country?->id,
            'total' => 100.00,
            'total_tax' => 21.00,
            'is_paid' => true,
            'created_by' => $systemUser->id,
            'updated_by' => $systemUser->id,
        ], $overrides));
    }

    protected function createTestUpload(
        Order $order,
        ?Material $material = null,
        array $overrides = []
    ): Upload {
        $material = $material ?? Material::first();

        return Upload::factory()->create(array_merge([
            'order_id' => $order->id,
            'wp_id' => (string) fake()->numberBetween(10000, 99999),
            'customer_id' => $order->customer_id,
            'currency_id' => $order->currency_id,
            'material_id' => $material?->id,
            'quantity' => 1,
            'total' => 50.00,
            'total_tax' => 10.50,
        ], $overrides));
    }

    /**
     * Create an order with one or more uploads in a single call.
     *
     * @return array{order: Order, uploads: Upload[]}
     */
    protected function createTestOrderWithUploads(
        int $uploadCount = 1,
        ?Customer $customer = null,
        ?Currency $currency = null,
        ?Country $country = null,
        ?Material $material = null,
        array $orderOverrides = [],
        array $uploadOverrides = []
    ): array {
        $order = $this->createTestOrder($customer, $currency, $country, $orderOverrides);

        $uploads = [];
        for ($i = 0; $i < $uploadCount; $i++) {
            $uploads[] = $this->createTestUpload($order, $material, $uploadOverrides);
        }

        return [
            'order' => $order,
            'uploads' => $uploads,
        ];
    }
}
