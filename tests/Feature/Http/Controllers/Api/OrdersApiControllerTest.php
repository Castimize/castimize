<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class OrdersApiControllerTest extends BaseTestCase
{
    use RefreshDatabase;

    public function testItStoresOrder(): void
    {
//        $response = $this->postJson(route('api.api.orders.store-order-wp'), $this->getWPOrderData());
//        dd($response);
    }

    private function getWPOrderData()
    {
        return json_decode(file_get_contents(storage_path('tests/wp-order.json')), true, 512, JSON_THROW_ON_ERROR);
    }
}
