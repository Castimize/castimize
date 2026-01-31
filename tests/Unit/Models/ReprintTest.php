<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\Reprint;
use App\Models\ReprintCulprit;
use App\Models\ReprintReason;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReprintTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $reprint = new Reprint;
        $fillable = $reprint->getFillable();

        $this->assertContains('manufacturer_id', $fillable);
        $this->assertContains('order_queue_id', $fillable);
        $this->assertContains('order_id', $fillable);
        $this->assertContains('reprint_culprit_id', $fillable);
        $this->assertContains('reprint_reason_id', $fillable);
        $this->assertContains('reason', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $reprint = new Reprint;
        $casts = $reprint->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_belongs_to_manufacturer(): void
    {
        $reprint = new Reprint;

        $this->assertInstanceOf(BelongsTo::class, $reprint->manufacturer());
        $this->assertEquals(Manufacturer::class, $reprint->manufacturer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order_queue(): void
    {
        $reprint = new Reprint;

        $this->assertInstanceOf(BelongsTo::class, $reprint->orderQueue());
        $this->assertEquals(OrderQueue::class, $reprint->orderQueue()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order(): void
    {
        $reprint = new Reprint;

        $this->assertInstanceOf(BelongsTo::class, $reprint->order());
        $this->assertEquals(Order::class, $reprint->order()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_reprint_culprit(): void
    {
        $reprint = new Reprint;

        $this->assertInstanceOf(BelongsTo::class, $reprint->reprintCulprit());
        $this->assertEquals(ReprintCulprit::class, $reprint->reprintCulprit()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_reprint_reason(): void
    {
        $reprint = new Reprint;

        $this->assertInstanceOf(BelongsTo::class, $reprint->reprintReason());
        $this->assertEquals(ReprintReason::class, $reprint->reprintReason()->getRelated()::class);
    }
}
