<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\Rejection;
use App\Models\RejectionReason;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RejectionTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $rejection = new Rejection;
        $fillable = $rejection->getFillable();

        $this->assertContains('manufacturer_id', $fillable);
        $this->assertContains('order_queue_id', $fillable);
        $this->assertContains('order_id', $fillable);
        $this->assertContains('upload_id', $fillable);
        $this->assertContains('rejection_reason_id', $fillable);
        $this->assertContains('reason_manufacturer', $fillable);
        $this->assertContains('note_manufacturer', $fillable);
        $this->assertContains('approved_at', $fillable);
        $this->assertContains('declined_at', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $rejection = new Rejection;
        $casts = $rejection->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['approved_at']);
        $this->assertEquals('datetime', $casts['declined_at']);
    }

    #[Test]
    public function it_belongs_to_manufacturer(): void
    {
        $rejection = new Rejection;

        $this->assertInstanceOf(BelongsTo::class, $rejection->manufacturer());
        $this->assertEquals(Manufacturer::class, $rejection->manufacturer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order_queue(): void
    {
        $rejection = new Rejection;

        $this->assertInstanceOf(BelongsTo::class, $rejection->orderQueue());
        $this->assertEquals(OrderQueue::class, $rejection->orderQueue()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order(): void
    {
        $rejection = new Rejection;

        $this->assertInstanceOf(BelongsTo::class, $rejection->order());
        $this->assertEquals(Order::class, $rejection->order()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_upload(): void
    {
        $rejection = new Rejection;

        $this->assertInstanceOf(BelongsTo::class, $rejection->upload());
        $this->assertEquals(Upload::class, $rejection->upload()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_rejection_reason(): void
    {
        $rejection = new Rejection;

        $this->assertInstanceOf(BelongsTo::class, $rejection->rejectionReason());
        $this->assertEquals(RejectionReason::class, $rejection->rejectionReason()->getRelated()::class);
    }
}
