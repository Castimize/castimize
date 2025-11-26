<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Enums\Shippo\ShippoCarriersEnum;

class ReceiptTrackingDTO
{
    public function __construct(
        public string $trackingCode,
        public string $carrier,
        public bool $sendBcc,
        public string $noteToBuyer,
    ) {
    }

    public static function from(
        string $trackingCode,
        string $carrier = ShippoCarriersEnum::UPS->value,
        bool $sendBcc = false,
        string $noteToBuyer = '',
    ): self {
        return new self(
            trackingCode: $trackingCode,
            carrier: $carrier,
            sendBcc: $sendBcc,
            noteToBuyer: $noteToBuyer,
        );
    }
}
