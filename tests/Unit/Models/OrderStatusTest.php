<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\OrderStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $orderStatus = new OrderStatus;
        $fillable = $orderStatus->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('end_status', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $orderStatus = new OrderStatus;
        $casts = $orderStatus->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_end_status_as_boolean(): void
    {
        $orderStatus = new OrderStatus;
        $casts = $orderStatus->getCasts();

        $this->assertEquals('boolean', $casts['end_status']);
    }
}
