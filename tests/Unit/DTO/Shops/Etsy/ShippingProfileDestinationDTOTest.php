<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Shops\Etsy;

use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShippingProfileDestinationDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new ShippingProfileDestinationDTO(
            shopId: 12345,
            shippingProfileId: 100,
            shippingProfileDestinationId: 200,
            primaryCost: 9.99,
            secondaryCost: 2.99,
            destinationCountryIso: 'NL',
            minDeliveryDays: 5,
            maxDeliveryDays: 10,
        );

        $this->assertEquals(12345, $dto->shopId);
        $this->assertEquals(100, $dto->shippingProfileId);
        $this->assertEquals(200, $dto->shippingProfileDestinationId);
        $this->assertEquals(9.99, $dto->primaryCost);
        $this->assertEquals(2.99, $dto->secondaryCost);
        $this->assertEquals('NL', $dto->destinationCountryIso);
        $this->assertEquals(5, $dto->minDeliveryDays);
        $this->assertEquals(10, $dto->maxDeliveryDays);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_destination_id(): void
    {
        $dto = new ShippingProfileDestinationDTO(
            shopId: 12345,
            shippingProfileId: 100,
            shippingProfileDestinationId: null,
            primaryCost: 15.00,
            secondaryCost: 5.00,
            destinationCountryIso: 'DE',
            minDeliveryDays: 3,
            maxDeliveryDays: 7,
        );

        $this->assertEquals(12345, $dto->shopId);
        $this->assertNull($dto->shippingProfileDestinationId);
        $this->assertEquals('DE', $dto->destinationCountryIso);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new ShippingProfileDestinationDTO(
            shopId: 999,
            shippingProfileId: 50,
            shippingProfileDestinationId: 75,
            primaryCost: 5.50,
            secondaryCost: 1.50,
            destinationCountryIso: 'GB',
            minDeliveryDays: 2,
            maxDeliveryDays: 5,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(999, $array['shopId']);
        $this->assertEquals(50, $array['shippingProfileId']);
        $this->assertEquals(75, $array['shippingProfileDestinationId']);
        $this->assertEquals(5.50, $array['primaryCost']);
        $this->assertEquals(1.50, $array['secondaryCost']);
        $this->assertEquals('GB', $array['destinationCountryIso']);
    }

    #[Test]
    public function it_can_be_created_from_array(): void
    {
        $dto = ShippingProfileDestinationDTO::from([
            'shopId' => 777,
            'shippingProfileId' => 25,
            'shippingProfileDestinationId' => 50,
            'primaryCost' => 8.00,
            'secondaryCost' => 3.00,
            'destinationCountryIso' => 'FR',
            'minDeliveryDays' => 3,
            'maxDeliveryDays' => 7,
        ]);

        $this->assertEquals(777, $dto->shopId);
        $this->assertEquals(25, $dto->shippingProfileId);
        $this->assertEquals('FR', $dto->destinationCountryIso);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new ShippingProfileDestinationDTO(
            shopId: 111,
            shippingProfileId: 10,
            shippingProfileDestinationId: 20,
            primaryCost: 12.00,
            secondaryCost: 4.00,
            destinationCountryIso: 'CA',
            minDeliveryDays: 7,
            maxDeliveryDays: 14,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(111, $decoded['shopId']);
        $this->assertEquals('CA', $decoded['destinationCountryIso']);
    }
}
