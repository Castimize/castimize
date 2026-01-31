<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\LogisticsZone;
use App\Models\ShippingFee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShippingFeeTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $shippingFee = new ShippingFee;
        $fillable = $shippingFee->getFillable();

        $this->assertContains('logistics_zone_id', $fillable);
        $this->assertContains('currency_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('default_rate', $fillable);
        $this->assertContains('default_lead_time', $fillable);
        $this->assertContains('cc_threshold_1', $fillable);
        $this->assertContains('rate_increase_1', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $shippingFee = new ShippingFee;
        $casts = $shippingFee->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_converts_default_rate_from_cents(): void
    {
        $shippingFee = new ShippingFee;
        $shippingFee->setRawAttributes(['default_rate' => 1500]);

        $this->assertEquals(15.00, $shippingFee->default_rate);
    }

    #[Test]
    public function it_converts_rate_increase_1_to_percentage(): void
    {
        // The accessor multiplies by 100 to convert from decimal to percentage
        // e.g., 0.10 (10%) stored in DB becomes 10.0 when retrieved
        $shippingFee = new ShippingFee;
        $shippingFee->setRawAttributes(['rate_increase_1' => 0.10]);

        $this->assertEquals(10.0, $shippingFee->rate_increase_1);
    }

    #[Test]
    public function it_converts_rate_increase_2_to_percentage(): void
    {
        $shippingFee = new ShippingFee;
        $shippingFee->setRawAttributes(['rate_increase_2' => 0.15]);

        $this->assertEquals(15.0, $shippingFee->rate_increase_2);
    }

    #[Test]
    public function it_converts_rate_increase_3_to_percentage(): void
    {
        $shippingFee = new ShippingFee;
        $shippingFee->setRawAttributes(['rate_increase_3' => 0.20]);

        $this->assertEquals(20.0, $shippingFee->rate_increase_3);
    }

    #[Test]
    public function it_belongs_to_logistics_zone(): void
    {
        $shippingFee = new ShippingFee;

        $this->assertInstanceOf(BelongsTo::class, $shippingFee->logisticsZone());
        $this->assertEquals(LogisticsZone::class, $shippingFee->logisticsZone()->getRelated()::class);
    }
}
