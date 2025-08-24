<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Admin;

use App\Services\Admin\OrdersService;
use Codexshaper\WooCommerce\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrdersServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrdersService $ordersService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ordersService = app(OrdersService::class);
        Bus::fake();
        Queue::fake();
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_creates_order_from_wp(): void
    {
        $wpOrder = new Order();
        $wpOrder->customer_id = 1;
        dd($wpOrder);
        $json = $this->getWPOrderData();
    }

    /**
     * @throws JsonException
     */
    private function getWPOrderData()
    {
        return json_decode(file_get_contents(storage_path('tests/wp-order.json')), false, 512, JSON_THROW_ON_ERROR);
    }
}
