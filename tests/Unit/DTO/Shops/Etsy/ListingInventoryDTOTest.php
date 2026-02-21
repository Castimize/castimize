<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Shops\Etsy;

use App\DTO\Shops\Etsy\ListingInventoryDTO;
use App\Enums\Admin\CurrencyEnum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListingInventoryDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new ListingInventoryDTO(
            listingId: 12345,
            sku: 'SKU-001',
            name: 'PLA White',
            price: 29.99,
            quantity: 100,
            currency: CurrencyEnum::USD,
            isEnabled: true,
        );

        $this->assertEquals(12345, $dto->listingId);
        $this->assertEquals('SKU-001', $dto->sku);
        $this->assertEquals('PLA White', $dto->name);
        $this->assertEquals(29.99, $dto->price);
        $this->assertEquals(100, $dto->quantity);
        $this->assertEquals(CurrencyEnum::USD, $dto->currency);
        $this->assertTrue($dto->isEnabled);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_listing_id(): void
    {
        $dto = new ListingInventoryDTO(
            listingId: null,
            sku: 'SKU-002',
            name: 'ABS Black',
            price: 19.99,
            quantity: 50,
            currency: CurrencyEnum::EUR,
            isEnabled: false,
        );

        $this->assertNull($dto->listingId);
        $this->assertEquals('SKU-002', $dto->sku);
        $this->assertEquals('ABS Black', $dto->name);
        $this->assertEquals(19.99, $dto->price);
        $this->assertEquals(CurrencyEnum::EUR, $dto->currency);
        $this->assertFalse($dto->isEnabled);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new ListingInventoryDTO(
            listingId: 999,
            sku: 'SKU-003',
            name: 'Nylon',
            price: 49.99,
            quantity: 25,
            currency: CurrencyEnum::EUR,
            isEnabled: true,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(999, $array['listingId']);
        $this->assertEquals('SKU-003', $array['sku']);
        $this->assertEquals('Nylon', $array['name']);
        $this->assertEquals(49.99, $array['price']);
        $this->assertEquals(25, $array['quantity']);
        $this->assertEquals('EUR', $array['currency']);
        $this->assertTrue($array['isEnabled']);
    }

    #[Test]
    public function it_can_be_created_from_array(): void
    {
        $dto = ListingInventoryDTO::from([
            'listingId' => 777,
            'sku' => 'SKU-004',
            'name' => 'Resin',
            'price' => 99.99,
            'quantity' => 10,
            'currency' => 'EUR',
            'isEnabled' => true,
        ]);

        $this->assertEquals(777, $dto->listingId);
        $this->assertEquals('SKU-004', $dto->sku);
        $this->assertEquals('Resin', $dto->name);
        $this->assertEquals(99.99, $dto->price);
        $this->assertEquals(10, $dto->quantity);
        $this->assertEquals(CurrencyEnum::EUR, $dto->currency);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new ListingInventoryDTO(
            listingId: 555,
            sku: 'SKU-JSON',
            name: 'PETG',
            price: 15.00,
            quantity: 5,
            currency: CurrencyEnum::USD,
            isEnabled: false,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(555, $decoded['listingId']);
        $this->assertEquals('SKU-JSON', $decoded['sku']);
        $this->assertEquals('PETG', $decoded['name']);
        $this->assertEquals(15.00, $decoded['price']);
    }

    #[Test]
    public function it_handles_zero_quantity(): void
    {
        $dto = new ListingInventoryDTO(
            listingId: 100,
            sku: 'SKU-ZERO',
            name: 'Out of Stock Material',
            price: 10.00,
            quantity: 0,
            currency: CurrencyEnum::USD,
            isEnabled: false,
        );

        $this->assertEquals(0, $dto->quantity);
        $this->assertFalse($dto->isEnabled);
    }

    #[Test]
    public function it_handles_decimal_prices(): void
    {
        $dto = new ListingInventoryDTO(
            listingId: 200,
            sku: 'SKU-DECIMAL',
            name: 'Precision Material',
            price: 123.456,
            quantity: 1,
            currency: CurrencyEnum::USD,
            isEnabled: true,
        );

        $this->assertEquals(123.456, $dto->price);
    }
}
