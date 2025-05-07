<?php

namespace App\Enums\Shippo;

enum ShippoCustomsDeclarationNonDeliveryOptionsEnum: string
{
    case ABANDON = 'ABANDON';
    case RETURN = 'RETURN';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
