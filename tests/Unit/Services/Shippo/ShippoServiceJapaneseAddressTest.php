<?php

namespace Tests\Unit\Services\Shippo;

use App\DTO\Shipping\AddressDTO;
use App\Nova\Settings\Shipping\CustomsItemSettings;
use App\Nova\Settings\Shipping\DcSettings;
use App\Nova\Settings\Shipping\GeneralSettings;
use App\Nova\Settings\Shipping\PickupSettings;
use App\Services\Shippo\ShippoService;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;
use Transliterator;

class ShippoServiceJapaneseAddressTest extends TestCase
{
    #[Test]
    public function set_to_address_sanitizes_japanese_street_lines_for_ups_compliance(): void
    {
        $service = new ShippoService(
            generalSettings: $this->createMock(GeneralSettings::class),
            customsItemSettings: $this->createMock(CustomsItemSettings::class),
            dcSettings: $this->createMock(DcSettings::class),
            pickupSettings: $this->createMock(PickupSettings::class),
        );

        $dto = new AddressDTO(
            name: '山田太郎',
            company: '株式会社テスト',
            street1: '〒150-0002 東京都渋谷区渋谷２丁目２１−１',
            street2: 'ヒカリエ 11F',
            street3: null,
            city: 'Tokyo',
            state: 'Tokyo',
            zip: '150-0002',
            country: 'JP',
            email: 'taro@example.com',
            phone: '0312345678',
        );

        $service->setToAddress($dto);
        $to = $service->getToAddress();

        $this->assertSame('JP', $to['country']);
        $this->assertArrayHasKey('street1', $to);

        foreach (['street1', 'street2'] as $key) {
            if (! array_key_exists($key, $to)) {
                continue;
            }

            $this->assertIsString($to[$key]);
            $this->assertNotSame('', $to[$key]);
            $this->assertLessThanOrEqual(35, strlen($to[$key]));
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9\s\-\.\/]+$/', $to[$key]);
        }

        if (array_key_exists('street3', $to)) {
            $this->assertLessThanOrEqual(35, strlen($to['street3']));
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9\s\-\.\/]+$/', $to['street3']);
        }
    }

    #[Test]
    public function japanese_address_more_than_three_lines_throws_when_transliterator_is_available(): void
    {
        if (! class_exists(Transliterator::class) || Transliterator::create('Any-Latin; Latin-ASCII') === null) {
            $this->markTestSkipped('Transliterator not available; sanitizeJapaneseAddress uses fallback path.');
        }

        $service = new ShippoService(
            generalSettings: $this->createMock(GeneralSettings::class),
            customsItemSettings: $this->createMock(CustomsItemSettings::class),
            dcSettings: $this->createMock(DcSettings::class),
            pickupSettings: $this->createMock(PickupSettings::class),
        );

        $dto = new AddressDTO(
            name: 'Taro',
            company: 'Test',
            street1: 'word01 word02 word03 word04 word05 word06 word07 word08 word09 word10 word11 word12 word13 word14 word15 word16 word17 word18 word19 word20 word21 word22 word23 word24 word25',
            street2: null,
            street3: null,
            city: 'Tokyo',
            state: 'Tokyo',
            zip: '150-0002',
            country: 'JP',
            email: 'taro@example.com',
            phone: '0312345678',
        );

        $this->expectException(RuntimeException::class);

        $service->setToAddress($dto);
    }
}
