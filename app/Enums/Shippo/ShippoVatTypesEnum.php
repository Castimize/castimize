<?php

namespace App\Enums\Shippo;

enum ShippoVatTypesEnum: string
{
    case EIN = 'EIN';
    case VAT = 'VAT';
    case IOSS = 'IOSS';
    case ARN = 'ARN';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
