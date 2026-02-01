<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\UploadDTO;
use App\Helpers\MonetaryAmount;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UploadDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $subtotal = MonetaryAmount::fromFloat(50.00);
        $subtotalTax = MonetaryAmount::fromFloat(10.50);
        $total = MonetaryAmount::fromFloat(50.00);
        $totalTax = MonetaryAmount::fromFloat(10.50);

        $dto = new UploadDTO(
            wpId: '12345',
            materialId: 1,
            materialName: 'PLA White',
            name: 'Model Name',
            fileName: 'model.stl',
            modelVolumeCc: 15.5,
            modelXLength: 10.0,
            modelYLength: 20.0,
            modelZLength: 5.0,
            modelBoxVolume: 1000.0,
            surfaceArea: 500.0,
            modelParts: 1,
            quantity: 2,
            inCents: false,
            subtotal: $subtotal,
            subtotalTax: $subtotalTax,
            total: $total,
            totalTax: $totalTax,
            metaData: ['key' => 'value'],
            customerLeadTime: 5,
        );

        $this->assertEquals('12345', $dto->wpId);
        $this->assertEquals(1, $dto->materialId);
        $this->assertEquals('PLA White', $dto->materialName);
        $this->assertEquals('Model Name', $dto->name);
        $this->assertEquals('model.stl', $dto->fileName);
        $this->assertEquals(15.5, $dto->modelVolumeCc);
        $this->assertEquals(10.0, $dto->modelXLength);
        $this->assertEquals(20.0, $dto->modelYLength);
        $this->assertEquals(5.0, $dto->modelZLength);
        $this->assertEquals(1000.0, $dto->modelBoxVolume);
        $this->assertEquals(500.0, $dto->surfaceArea);
        $this->assertEquals(1, $dto->modelParts);
        $this->assertEquals(2, $dto->quantity);
        $this->assertFalse($dto->inCents);
        $this->assertEquals($subtotal, $dto->subtotal);
        $this->assertEquals($subtotalTax, $dto->subtotalTax);
        $this->assertEquals($total, $dto->total);
        $this->assertEquals($totalTax, $dto->totalTax);
        $this->assertEquals(['key' => 'value'], $dto->metaData);
        $this->assertEquals(5, $dto->customerLeadTime);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_parameters(): void
    {
        $subtotal = MonetaryAmount::fromFloat(25.00);
        $total = MonetaryAmount::fromFloat(25.00);

        $dto = new UploadDTO(
            wpId: null,
            materialId: null,
            materialName: null,
            name: 'Simple Model',
            fileName: 'simple.stl',
            modelVolumeCc: 10.0,
            modelXLength: 5.0,
            modelYLength: 5.0,
            modelZLength: 5.0,
            modelBoxVolume: 125.0,
            surfaceArea: 150.0,
            modelParts: 1,
            quantity: 1,
            inCents: false,
            subtotal: $subtotal,
            subtotalTax: null,
            total: $total,
            totalTax: null,
            metaData: null,
            customerLeadTime: 3,
        );

        $this->assertNull($dto->wpId);
        $this->assertNull($dto->materialId);
        $this->assertNull($dto->materialName);
        $this->assertNull($dto->subtotalTax);
        $this->assertNull($dto->totalTax);
        $this->assertNull($dto->metaData);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new UploadDTO(
            wpId: '999',
            materialId: 2,
            materialName: 'ABS Black',
            name: 'Test Model',
            fileName: 'test.stl',
            modelVolumeCc: 20.0,
            modelXLength: 15.0,
            modelYLength: 15.0,
            modelZLength: 10.0,
            modelBoxVolume: 2250.0,
            surfaceArea: 800.0,
            modelParts: 2,
            quantity: 3,
            inCents: true,
            subtotal: MonetaryAmount::fromFloat(100.00),
            subtotalTax: MonetaryAmount::fromFloat(21.00),
            total: MonetaryAmount::fromFloat(100.00),
            totalTax: MonetaryAmount::fromFloat(21.00),
            metaData: [],
            customerLeadTime: 7,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('999', $array['wpId']);
        $this->assertEquals(2, $array['materialId']);
        $this->assertEquals('ABS Black', $array['materialName']);
        $this->assertEquals('Test Model', $array['name']);
        $this->assertEquals('test.stl', $array['fileName']);
        $this->assertEquals(20.0, $array['modelVolumeCc']);
        $this->assertEquals(2, $array['modelParts']);
        $this->assertEquals(3, $array['quantity']);
        $this->assertTrue($array['inCents']);
        $this->assertEquals(7, $array['customerLeadTime']);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new UploadDTO(
            wpId: '555',
            materialId: 3,
            materialName: 'Nylon',
            name: 'JSON Model',
            fileName: 'json.stl',
            modelVolumeCc: 5.0,
            modelXLength: 3.0,
            modelYLength: 3.0,
            modelZLength: 3.0,
            modelBoxVolume: 27.0,
            surfaceArea: 54.0,
            modelParts: 1,
            quantity: 1,
            inCents: false,
            subtotal: MonetaryAmount::fromFloat(75.00),
            subtotalTax: null,
            total: MonetaryAmount::fromFloat(75.00),
            totalTax: null,
            metaData: null,
            customerLeadTime: 4,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('555', $decoded['wpId']);
        $this->assertEquals('JSON Model', $decoded['name']);
    }

    #[Test]
    public function it_handles_cents_mode(): void
    {
        $dto = new UploadDTO(
            wpId: '100',
            materialId: 1,
            materialName: 'PLA',
            name: 'Cents Model',
            fileName: 'cents.stl',
            modelVolumeCc: 10.0,
            modelXLength: 5.0,
            modelYLength: 5.0,
            modelZLength: 5.0,
            modelBoxVolume: 125.0,
            surfaceArea: 150.0,
            modelParts: 1,
            quantity: 1,
            inCents: true,
            subtotal: MonetaryAmount::fromCents(5000),
            subtotalTax: MonetaryAmount::fromCents(1050),
            total: MonetaryAmount::fromCents(5000),
            totalTax: MonetaryAmount::fromCents(1050),
            metaData: null,
            customerLeadTime: 5,
        );

        $this->assertTrue($dto->inCents);
        $this->assertEquals(50.00, $dto->subtotal->toFloat());
        $this->assertEquals(10.50, $dto->subtotalTax->toFloat());
    }

    #[Test]
    public function it_handles_large_models(): void
    {
        $dto = new UploadDTO(
            wpId: '200',
            materialId: 5,
            materialName: 'Resin',
            name: 'Large Model',
            fileName: 'large.stl',
            modelVolumeCc: 5000.0,
            modelXLength: 100.0,
            modelYLength: 100.0,
            modelZLength: 50.0,
            modelBoxVolume: 500000.0,
            surfaceArea: 30000.0,
            modelParts: 10,
            quantity: 1,
            inCents: false,
            subtotal: MonetaryAmount::fromFloat(2500.00),
            subtotalTax: MonetaryAmount::fromFloat(525.00),
            total: MonetaryAmount::fromFloat(2500.00),
            totalTax: MonetaryAmount::fromFloat(525.00),
            metaData: null,
            customerLeadTime: 14,
        );

        $this->assertEquals(5000.0, $dto->modelVolumeCc);
        $this->assertEquals(500000.0, $dto->modelBoxVolume);
        $this->assertEquals(10, $dto->modelParts);
    }

    #[Test]
    public function it_handles_multiple_quantities(): void
    {
        $dto = new UploadDTO(
            wpId: '300',
            materialId: 1,
            materialName: 'PLA',
            name: 'Bulk Order',
            fileName: 'bulk.stl',
            modelVolumeCc: 5.0,
            modelXLength: 3.0,
            modelYLength: 3.0,
            modelZLength: 3.0,
            modelBoxVolume: 27.0,
            surfaceArea: 54.0,
            modelParts: 1,
            quantity: 100,
            inCents: false,
            subtotal: MonetaryAmount::fromFloat(1000.00),
            subtotalTax: MonetaryAmount::fromFloat(210.00),
            total: MonetaryAmount::fromFloat(1000.00),
            totalTax: MonetaryAmount::fromFloat(210.00),
            metaData: null,
            customerLeadTime: 10,
        );

        $this->assertEquals(100, $dto->quantity);
    }
}
