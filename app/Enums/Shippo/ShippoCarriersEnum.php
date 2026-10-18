<?php

namespace App\Enums\Shippo;

enum ShippoCarriersEnum: string
{
    case UPS = 'ups';
    case FedEx = 'fedex';

    public static function default(): self
    {
        return self::from(config('services.shippo.default_carrier', self::UPS->value));
    }

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
