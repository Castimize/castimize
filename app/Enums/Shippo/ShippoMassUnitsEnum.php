<?php

namespace App\Enums\Shippo;

enum ShippoMassUnitsEnum: string
{
    case G = 'g';
    case OZ = 'oz';
    case LB = 'lb';
    case KG = 'kg';

    public static function values(): array
    {
        return [
            self::G->value => 'G',
            self::OZ->value => 'Oz',
            self::LB->value => 'Lb',
            self::KG->value => 'Kg',
        ];
    }
}
