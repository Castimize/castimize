<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Shippo;

use App\Enums\Shippo\ShippoCarriersEnum;
use App\Nova\Settings\Shipping\CustomsItemSettings;
use App\Nova\Settings\Shipping\DcSettings;
use App\Nova\Settings\Shipping\GeneralSettings;
use App\Nova\Settings\Shipping\PickupSettings;
use App\Services\Shippo\ShippoService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShippoServiceTest extends TestCase
{
    private GeneralSettings $generalSettings;

    private ShippoService $shippoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generalSettings = Mockery::mock(GeneralSettings::class);
        $this->setSettingsProperty($this->generalSettings, 'upsCarrierAccount', 'ups-account-id-123');
        $this->setSettingsProperty($this->generalSettings, 'fedexCarrierAccount', 'fedex-account-id-456');
        $this->setSettingsProperty($this->generalSettings, 'defaultCarrier', ShippoCarriersEnum::UPS->value);

        $this->shippoService = new ShippoService(
            generalSettings: $this->generalSettings,
            customsItemSettings: app(CustomsItemSettings::class),
            dcSettings: app(DcSettings::class),
            pickupSettings: app(PickupSettings::class),
        );
    }

    #[Test]
    public function it_returns_ups_account_by_default(): void
    {
        $account = $this->shippoService->getCarrierAccount();

        $this->assertEquals('ups-account-id-123', $account);
    }

    #[Test]
    public function it_returns_ups_account_when_ups_carrier_specified(): void
    {
        $account = $this->shippoService->getCarrierAccount(ShippoCarriersEnum::UPS->value);

        $this->assertEquals('ups-account-id-123', $account);
    }

    #[Test]
    public function it_returns_fedex_account_when_fedex_carrier_specified(): void
    {
        $account = $this->shippoService->getCarrierAccount(ShippoCarriersEnum::FedEx->value);

        $this->assertEquals('fedex-account-id-456', $account);
    }

    #[Test]
    public function it_uses_default_carrier_from_settings_when_no_carrier_passed(): void
    {
        $this->setSettingsProperty($this->generalSettings, 'defaultCarrier', ShippoCarriersEnum::FedEx->value);

        $account = $this->shippoService->getCarrierAccount(null);

        $this->assertEquals('fedex-account-id-456', $account);
    }

    #[Test]
    public function it_falls_back_to_ups_when_default_carrier_not_set(): void
    {
        $this->setSettingsProperty($this->generalSettings, 'defaultCarrier', null);

        $account = $this->shippoService->getCarrierAccount(null);

        $this->assertEquals('ups-account-id-123', $account);
    }

    #[Test]
    public function it_has_fedex_in_carriers_list(): void
    {
        $carriers = $this->shippoService->getCarriers();

        $this->assertArrayHasKey('fedex', $carriers);
        $this->assertEquals('FedEx', $carriers['fedex']);
    }

    #[Test]
    public function it_has_ups_in_carriers_list(): void
    {
        $carriers = $this->shippoService->getCarriers();

        $this->assertArrayHasKey('ups', $carriers);
        $this->assertEquals('UPS', $carriers['ups']);
    }

    #[Test]
    public function it_has_fedex_services_in_services_list(): void
    {
        $services = $this->shippoService->getServices();

        $this->assertArrayHasKey('fedex_ground', $services);
        $this->assertArrayHasKey('fedex_international_economy', $services);
        $this->assertArrayHasKey('fedex_international_priority', $services);
        $this->assertArrayHasKey('fedex_regional_economy', $services);
        $this->assertArrayHasKey('fedex_priority_overnight', $services);
    }

    #[Test]
    public function it_still_has_ups_services_in_services_list(): void
    {
        $services = $this->shippoService->getServices();

        $this->assertArrayHasKey('ups_standard', $services);
        $this->assertArrayHasKey('ups_express', $services);
        $this->assertArrayHasKey('ups_saver', $services);
    }

    #[Test]
    public function it_has_fedex_package_types(): void
    {
        $this->assertArrayHasKey('FedEx_Envelope', $this->shippoService->packageTypes);
        $this->assertArrayHasKey('FedEx_Pak', $this->shippoService->packageTypes);
        $this->assertArrayHasKey('FedEx_Box', $this->shippoService->packageTypes);
    }

    #[Test]
    public function it_still_has_ups_package_types(): void
    {
        $this->assertArrayHasKey('UPS_Express_Envelope', $this->shippoService->packageTypes);
        $this->assertArrayHasKey('UPS_Express_Pak', $this->shippoService->packageTypes);
    }

    private function setSettingsProperty(object $settings, string $property, mixed $value): void
    {
        $prop = new \ReflectionProperty(GeneralSettings::class, $property);
        $prop->setAccessible(true);
        $prop->setValue($settings, $value);
    }
}
