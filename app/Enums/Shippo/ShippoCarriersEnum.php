<?php

namespace App\Enums\Shippo;

enum ShippoCarriersEnum: string
{
    case UPS = 'ups';

    public static function values(): array
    {
        return [
            self::UPS->value => 'UPS',
        ];
    }
}
