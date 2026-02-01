<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\CalculateShippingFeeUploadDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalculateShippingFeeUploadDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new CalculateShippingFeeUploadDTO(
            modelBoxVolume: 125.50,
            quantity: 5,
        );

        $this->assertEquals(125.50, $dto->modelBoxVolume);
        $this->assertEquals(5, $dto->quantity);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new CalculateShippingFeeUploadDTO(
            modelBoxVolume: 200.00,
            quantity: 10,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(200.00, $array['modelBoxVolume']);
        $this->assertEquals(10, $array['quantity']);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new CalculateShippingFeeUploadDTO(
            modelBoxVolume: 50.00,
            quantity: 1,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(50.00, $decoded['modelBoxVolume']);
        $this->assertEquals(1, $decoded['quantity']);
    }

    #[Test]
    public function it_handles_zero_volume(): void
    {
        $dto = new CalculateShippingFeeUploadDTO(
            modelBoxVolume: 0.0,
            quantity: 1,
        );

        $this->assertEquals(0.0, $dto->modelBoxVolume);
    }

    #[Test]
    public function it_handles_large_quantities(): void
    {
        $dto = new CalculateShippingFeeUploadDTO(
            modelBoxVolume: 10.0,
            quantity: 1000,
        );

        $this->assertEquals(10.0, $dto->modelBoxVolume);
        $this->assertEquals(1000, $dto->quantity);
    }

    #[Test]
    public function it_handles_decimal_volumes(): void
    {
        $dto = new CalculateShippingFeeUploadDTO(
            modelBoxVolume: 123.456789,
            quantity: 2,
        );

        $this->assertEquals(123.456789, $dto->modelBoxVolume);
    }
}
