<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Complaint;
use App\Models\ComplaintReason;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $complaint = new Complaint;
        $fillable = $complaint->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('complaint_reason_id', $fillable);
        $this->assertContains('upload_id', $fillable);
        $this->assertContains('order_id', $fillable);
        $this->assertContains('deny_at', $fillable);
        $this->assertContains('reprint_at', $fillable);
        $this->assertContains('refund_at', $fillable);
        $this->assertContains('reason', $fillable);
        $this->assertContains('description', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $complaint = new Complaint;
        $casts = $complaint->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['deny_at']);
        $this->assertEquals('datetime', $casts['reprint_at']);
        $this->assertEquals('datetime', $casts['refund_at']);
    }

    #[Test]
    public function it_belongs_to_customer(): void
    {
        $complaint = new Complaint;

        $this->assertInstanceOf(BelongsTo::class, $complaint->customer());
        $this->assertEquals(Customer::class, $complaint->customer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_complaint_reason(): void
    {
        $complaint = new Complaint;

        $this->assertInstanceOf(BelongsTo::class, $complaint->complaintReason());
        $this->assertEquals(ComplaintReason::class, $complaint->complaintReason()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order(): void
    {
        $complaint = new Complaint;

        $this->assertInstanceOf(BelongsTo::class, $complaint->order());
        $this->assertEquals(Order::class, $complaint->order()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_upload(): void
    {
        $complaint = new Complaint;

        $this->assertInstanceOf(BelongsTo::class, $complaint->upload());
        $this->assertEquals(Upload::class, $complaint->upload()->getRelated()::class);
    }
}
