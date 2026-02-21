<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\OrderQueue;
use App\Models\OrderQueueStatus;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderQueueStatusTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $orderQueueStatus = new OrderQueueStatus;
        $fillable = $orderQueueStatus->getFillable();

        $this->assertContains('order_queue_id', $fillable);
        $this->assertContains('order_status_id', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('target_date', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $orderQueueStatus = new OrderQueueStatus;
        $casts = $orderQueueStatus->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['target_date']);
    }

    #[Test]
    public function it_belongs_to_order_queue(): void
    {
        $orderQueueStatus = new OrderQueueStatus;

        $this->assertInstanceOf(BelongsTo::class, $orderQueueStatus->orderQueue());
        $this->assertEquals(OrderQueue::class, $orderQueueStatus->orderQueue()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order_status(): void
    {
        $orderQueueStatus = new OrderQueueStatus;

        $this->assertInstanceOf(BelongsTo::class, $orderQueueStatus->orderStatus());
        $this->assertEquals(OrderStatus::class, $orderQueueStatus->orderStatus()->getRelated()::class);
    }
}
