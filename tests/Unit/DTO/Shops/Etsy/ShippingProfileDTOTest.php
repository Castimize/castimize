<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Shops\Etsy;

use App\DTO\Shops\Etsy\ShippingProfileDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShippingProfileDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new ShippingProfileDTO(
            shopId: 12345,
            shippingProfileId: 100,
            title: 'Standard Shipping',
            originCountryIso: 'NL',
            primaryCost: 10.00,
            secondaryCost: 3.00,
            destinationCountryIso: 'US',
            originPostalCode: '1011 AB',
            minProcessingTime: 1,
            maxProcessingTime: 3,
            processingTimeUnit: 'weeks',
            minDeliveryDays: 5,
            maxDeliveryDays: 10,
            shippingProfileDestinations: null,
        );

        $this->assertEquals(12345, $dto->shopId);
        $this->assertEquals(100, $dto->shippingProfileId);
        $this->assertEquals('Standard Shipping', $dto->title);
        $this->assertEquals('NL', $dto->originCountryIso);
        $this->assertEquals(10.00, $dto->primaryCost);
        $this->assertEquals(3.00, $dto->secondaryCost);
        $this->assertEquals('US', $dto->destinationCountryIso);
        $this->assertEquals('1011 AB', $dto->originPostalCode);
        $this->assertEquals(1, $dto->minProcessingTime);
        $this->assertEquals(3, $dto->maxProcessingTime);
        $this->assertEquals('weeks', $dto->processingTimeUnit);
        $this->assertEquals(5, $dto->minDeliveryDays);
        $this->assertEquals(10, $dto->maxDeliveryDays);
        $this->assertNull($dto->shippingProfileDestinations);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_profile_id(): void
    {
        $dto = new ShippingProfileDTO(
            shopId: 999,
            shippingProfileId: null,
            title: 'Express Shipping',
            originCountryIso: 'NL',
            primaryCost: 15.00,
            secondaryCost: 5.00,
            destinationCountryIso: 'DE',
            originPostalCode: '2000 BB',
            minProcessingTime: 1,
            maxProcessingTime: 2,
            processingTimeUnit: 'weeks',
            minDeliveryDays: 2,
            maxDeliveryDays: 5,
            shippingProfileDestinations: null,
        );

        $this->assertEquals(999, $dto->shopId);
        $this->assertNull($dto->shippingProfileId);
        $this->assertEquals('Express Shipping', $dto->title);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new ShippingProfileDTO(
            shopId: 555,
            shippingProfileId: 50,
            title: 'Priority Shipping',
            originCountryIso: 'NL',
            primaryCost: 12.00,
            secondaryCost: 4.00,
            destinationCountryIso: 'GB',
            originPostalCode: '3000 CC',
            minProcessingTime: 1,
            maxProcessingTime: 2,
            processingTimeUnit: 'weeks',
            minDeliveryDays: 3,
            maxDeliveryDays: 7,
            shippingProfileDestinations: null,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(555, $array['shopId']);
        $this->assertEquals(50, $array['shippingProfileId']);
        $this->assertEquals('Priority Shipping', $array['title']);
        $this->assertEquals('NL', $array['originCountryIso']);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new ShippingProfileDTO(
            shopId: 333,
            shippingProfileId: 25,
            title: 'Economy Shipping',
            originCountryIso: 'NL',
            primaryCost: 8.00,
            secondaryCost: 2.00,
            destinationCountryIso: 'FR',
            originPostalCode: '4000 DD',
            minProcessingTime: 2,
            maxProcessingTime: 5,
            processingTimeUnit: 'weeks',
            minDeliveryDays: 7,
            maxDeliveryDays: 14,
            shippingProfileDestinations: null,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(333, $decoded['shopId']);
        $this->assertEquals('Economy Shipping', $decoded['title']);
    }

    #[Test]
    public function it_can_be_created_from_array(): void
    {
        $dto = ShippingProfileDTO::from([
            'shopId' => 777,
            'shippingProfileId' => 75,
            'title' => 'From Array Shipping',
            'originCountryIso' => 'NL',
            'primaryCost' => 10.00,
            'secondaryCost' => 3.00,
            'destinationCountryIso' => 'US',
            'originPostalCode' => '5000 EE',
            'minProcessingTime' => 1,
            'maxProcessingTime' => 3,
            'processingTimeUnit' => 'weeks',
            'minDeliveryDays' => 5,
            'maxDeliveryDays' => 10,
            'shippingProfileDestinations' => null,
        ]);

        $this->assertEquals(777, $dto->shopId);
        $this->assertEquals(75, $dto->shippingProfileId);
        $this->assertEquals('From Array Shipping', $dto->title);
    }
}
