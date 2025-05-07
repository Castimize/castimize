<?php

namespace App\Enums\Shippo;

enum ShippoContentTypesEnum: string
{
    case DOCUMENTS = 'DOCUMENTS';
    case GIFT = 'GIFT';
    case SAMPLE = 'SAMPLE';
    case MERCHANDISE = 'MERCHANDISE';
    case HUMANITARIAN_DONATION = 'HUMANITARIAN_DONATION';
    case RETURN_MERCHANDISE = 'RETURN_MERCHANDISE';
    case OTHER = 'OTHER';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
