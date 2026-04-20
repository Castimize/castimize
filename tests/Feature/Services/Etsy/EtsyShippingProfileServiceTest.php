<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Etsy;

use App\Models\Country;
use App\Models\LogisticsZone;
use App\Models\ShippingFee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EtsyShippingProfileServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_includes_countries_with_logistics_zone_and_shipping_fee(): void
    {
        $zone = LogisticsZone::factory()->create();
        ShippingFee::factory()->create(['logistics_zone_id' => $zone->id]);
        $country = Country::factory()->create(['logistics_zone_id' => $zone->id]);

        $ids = Country::whereHas('logisticsZone', fn ($q) => $q->whereHas('shippingFee'))->pluck('id');

        $this->assertContains($country->id, $ids);
    }

    #[Test]
    public function it_excludes_countries_without_logistics_zone(): void
    {
        $country = Country::factory()->create(['logistics_zone_id' => null]);

        $ids = Country::whereHas('logisticsZone', fn ($q) => $q->whereHas('shippingFee'))->pluck('id');

        $this->assertNotContains($country->id, $ids);
    }

    #[Test]
    public function it_excludes_countries_with_logistics_zone_but_no_shipping_fee(): void
    {
        $zone = LogisticsZone::factory()->create();
        $country = Country::factory()->create(['logistics_zone_id' => $zone->id]);

        $ids = Country::whereHas('logisticsZone', fn ($q) => $q->whereHas('shippingFee'))->pluck('id');

        $this->assertNotContains($country->id, $ids);
    }
}
