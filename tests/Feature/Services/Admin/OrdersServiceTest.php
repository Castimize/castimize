<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Admin;

use App\Services\Admin\OrdersService;
use Codexshaper\WooCommerce\Facades\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsWpOrder;

class OrdersServiceTest extends TestCase
{
    use NeedsWpOrder;
    use RefreshDatabase;

    private OrdersService $ordersService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ordersService = app(OrdersService::class);
        Bus::fake();
        Queue::fake();
        Event::fake();
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_creates_order_from_wp(): void
    {
        $wpOrder = Order::find(3324);
        $this->ordersService->storeOrderFromWpOrder($wpOrder);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'order_number' => $wpOrder->order_number,
        ]);
    }

    /**
     * @throws JsonException
     */
    private function getWPOrderData()
    {
        return json_decode(file_get_contents(storage_path('tests/wp-order.json')), false, 512, JSON_THROW_ON_ERROR);
    }
}
