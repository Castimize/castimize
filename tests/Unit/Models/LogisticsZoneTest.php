<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Country;
use App\Models\LogisticsZone;
use App\Models\ShippingFee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogisticsZoneTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $logisticsZone = new LogisticsZone;
        $fillable = $logisticsZone->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('shipping_servicelevel_token', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $logisticsZone = new LogisticsZone;
        $casts = $logisticsZone->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_has_one_shipping_fee(): void
    {
        $logisticsZone = new LogisticsZone;

        $this->assertInstanceOf(HasOne::class, $logisticsZone->shippingFee());
        $this->assertEquals(ShippingFee::class, $logisticsZone->shippingFee()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_countries(): void
    {
        $logisticsZone = new LogisticsZone;

        $this->assertInstanceOf(BelongsTo::class, $logisticsZone->countries());
        $this->assertEquals(Country::class, $logisticsZone->countries()->getRelated()::class);
    }
}
