<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\CheckOrderAllRejected;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\Rejection;
use App\Models\ShippingFee;
use App\Models\Upload;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckOrderAllRejectedTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    private Currency $currency;

    private Customer $customer;

    private Manufacturer $manufacturer;

    private ShippingFee $shippingFee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );

        $this->customer = Customer::factory()->create();

        $this->manufacturer = Manufacturer::factory()->create();

        $this->shippingFee = ShippingFee::factory()->create([
            'currency_id' => $this->currency->id,
        ]);

        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'currency_id' => $this->currency->id,
            'has_manual_refund' => false,
            'wp_id' => 12345,
        ]);
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        CheckOrderAllRejected::dispatch($this->order);

        Queue::assertPushed(CheckOrderAllRejected::class);
    }

    #[Test]
    public function it_clears_cache_after_processing_order_with_manual_refund(): void
    {
        // Order with manual refund won't call WooCommerce API
        $this->order->update(['has_manual_refund' => true]);

        $cacheKey = sprintf('create-order-all-rejected-job-%s', $this->order->id);
        Cache::put($cacheKey, true);

        $job = new CheckOrderAllRejected($this->order);
        $job->handle();

        $this->assertNull(Cache::get($cacheKey));
    }

    #[Test]
    public function it_skips_refund_handling_when_has_manual_refund(): void
    {
        $this->order->update(['has_manual_refund' => true]);

        $upload = Upload::factory()->create([
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'currency_id' => $this->currency->id,
        ]);

        $orderQueue = OrderQueue::factory()->create([
            'order_id' => $this->order->id,
            'upload_id' => $upload->id,
            'manufacturer_id' => $this->manufacturer->id,
            'shipping_fee_id' => $this->shippingFee->id,
        ]);

        Rejection::factory()->create([
            'order_queue_id' => $orderQueue->id,
            'order_id' => $this->order->id,
            'upload_id' => $upload->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        // Should not throw exception even with rejections
        $job = new CheckOrderAllRejected($this->order);
        $job->handle();

        $this->assertTrue(true); // Job completed without exception
    }
}
