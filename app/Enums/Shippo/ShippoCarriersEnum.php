<?php

namespace App\Enums\Shippo;

enum ShippoCarriersEnum: string
{
    case UPS = 'ups';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
