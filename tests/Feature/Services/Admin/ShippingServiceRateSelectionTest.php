<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Admin;

use App\Models\Country;
use App\Models\LogisticsZone;
use App\Services\Admin\ShippingService;
use App\Services\Shippo\ShippoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class ShippingServiceRateSelectionTest extends TestCase
{
    use DatabaseTransactions;

    private ShippingService $shippingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(ShippoService::class, Mockery::mock(ShippoService::class));
        $this->shippingService = app(ShippingService::class);
    }

    /**
     * @param  array<string, mixed>  $zoneAttributes
     */
    private function createCountryInZone(array $zoneAttributes): void
    {
        $zone = LogisticsZone::create(array_merge(['name' => 'Rate test zone'], $zoneAttributes));
        Country::updateOrCreate(
            ['alpha2' => 'US'],
            ['name' => 'United States', 'alpha3' => 'USA', 'logistics_zone_id' => $zone->id]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function fedexShipment(): array
    {
        $rates = [
            ['international_first', '252.23'],
            ['international_connect_plus', '22.26'],
            ['international_priority', '22.08'],
            ['international_economy', '18.80'],
        ];

        return ['rates' => array_map(fn (array $rate) => [
            'object_id' => $rate[0],
            'amount' => $rate[1],
            'servicelevel' => ['token' => 'fedex_'.$rate[0]],
        ], $rates)];
    }

    /**
     * @param  array<string, mixed>  $shipment
     * @return array<string, mixed>|null
     */
    private function selectRate(array $shipment, ?string $carrier): ?array
    {
        $method = new ReflectionMethod(ShippingService::class, 'getCustomerShipmentRate');

        return $method->invoke($this->shippingService, $shipment, 'US', $carrier);
    }

    #[Test]
    public function it_uses_the_fedex_service_level_configured_on_the_zone(): void
    {
        $this->createCountryInZone([
            'shipping_servicelevel_token' => 'ups_saver',
            'fedex_shipping_servicelevel_token' => 'fedex_international_priority',
        ]);

        $rate = $this->selectRate($this->fedexShipment(), 'fedex');

        $this->assertSame('international_priority', $rate['object_id']);
    }

    #[Test]
    public function it_picks_the_cheapest_fedex_rate_when_the_zone_has_no_fedex_service_level(): void
    {
        $this->createCountryInZone(['shipping_servicelevel_token' => 'ups_saver']);

        $rate = $this->selectRate($this->fedexShipment(), 'fedex');

        $this->assertSame('international_economy', $rate['object_id']);
    }

    #[Test]
    public function it_picks_the_cheapest_rate_when_the_configured_service_level_is_not_offered(): void
    {
        $this->createCountryInZone([
            'shipping_servicelevel_token' => 'ups_saver',
            'fedex_shipping_servicelevel_token' => 'fedex_priority_overnight',
        ]);

        $rate = $this->selectRate($this->fedexShipment(), 'fedex');

        $this->assertSame('international_economy', $rate['object_id']);
    }

    #[Test]
    public function it_still_uses_the_default_service_level_for_other_carriers(): void
    {
        $this->createCountryInZone([
            'shipping_servicelevel_token' => 'ups_saver',
            'fedex_shipping_servicelevel_token' => 'fedex_international_economy',
        ]);
        $shipment = ['rates' => [
            ['object_id' => 'ups-standard', 'amount' => '30.00', 'servicelevel' => ['token' => 'ups_standard']],
            ['object_id' => 'ups-saver', 'amount' => '35.00', 'servicelevel' => ['token' => 'ups_saver']],
        ]];

        $this->assertSame('ups-saver', $this->selectRate($shipment, 'ups')['object_id']);
        $this->assertSame('ups-saver', $this->selectRate($shipment, null)['object_id']);
    }

    #[Test]
    public function it_returns_null_when_there_are_no_rates(): void
    {
        $this->createCountryInZone(['shipping_servicelevel_token' => 'ups_saver']);

        $this->assertNull($this->selectRate(['rates' => []], 'fedex'));
    }
}
