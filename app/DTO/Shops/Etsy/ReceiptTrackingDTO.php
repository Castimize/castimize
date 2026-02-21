<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Enums\Shippo\ShippoCarriersEnum;
use Spatie\LaravelData\Data;

class ReceiptTrackingDTO extends Data
{
    public function __construct(
        public string $trackingCode,
        public string $carrier = ShippoCarriersEnum::UPS->value,
        public bool $sendBcc = false,
        public string $noteToBuyer = '',
    ) {}
}
