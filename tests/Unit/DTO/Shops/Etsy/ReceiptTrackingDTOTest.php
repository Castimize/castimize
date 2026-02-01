<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Shops\Etsy;

use App\DTO\Shops\Etsy\ReceiptTrackingDTO;
use App\Enums\Shippo\ShippoCarriersEnum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReceiptTrackingDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new ReceiptTrackingDTO(
            trackingCode: 'TRACK123',
            carrier: 'fedex',
            sendBcc: true,
            noteToBuyer: 'Your package is on the way!',
        );

        $this->assertEquals('TRACK123', $dto->trackingCode);
        $this->assertEquals('fedex', $dto->carrier);
        $this->assertTrue($dto->sendBcc);
        $this->assertEquals('Your package is on the way!', $dto->noteToBuyer);
    }

    #[Test]
    public function it_uses_default_carrier_when_not_provided(): void
    {
        $dto = new ReceiptTrackingDTO(
            trackingCode: 'TRACK456',
        );

        $this->assertEquals('TRACK456', $dto->trackingCode);
        $this->assertEquals(ShippoCarriersEnum::UPS->value, $dto->carrier);
        $this->assertFalse($dto->sendBcc);
        $this->assertEquals('', $dto->noteToBuyer);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new ReceiptTrackingDTO(
            trackingCode: 'TRACK789',
            carrier: 'dhl',
            sendBcc: false,
            noteToBuyer: 'Shipping notification',
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('TRACK789', $array['trackingCode']);
        $this->assertEquals('dhl', $array['carrier']);
        $this->assertFalse($array['sendBcc']);
        $this->assertEquals('Shipping notification', $array['noteToBuyer']);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new ReceiptTrackingDTO(
            trackingCode: 'TRACK999',
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('TRACK999', $decoded['trackingCode']);
    }

    #[Test]
    public function it_can_be_created_from_array(): void
    {
        $dto = ReceiptTrackingDTO::from([
            'trackingCode' => 'TRACK111',
            'carrier' => 'usps',
            'sendBcc' => true,
            'noteToBuyer' => 'Thanks for your order!',
        ]);

        $this->assertEquals('TRACK111', $dto->trackingCode);
        $this->assertEquals('usps', $dto->carrier);
        $this->assertTrue($dto->sendBcc);
        $this->assertEquals('Thanks for your order!', $dto->noteToBuyer);
    }
}
