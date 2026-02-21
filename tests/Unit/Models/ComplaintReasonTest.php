<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ComplaintReason;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComplaintReasonTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $complaintReason = new ComplaintReason;
        $fillable = $complaintReason->getFillable();

        $this->assertContains('reason', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $complaintReason = new ComplaintReason;
        $casts = $complaintReason->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }
}
