<?php

namespace App\Enums\Shippo;

enum ShippoDistanceUnitsEnum: string
{
    case CM = 'cm';
    case IN = 'in';
    case FT = 'ft';
    case MM = 'mm';
    case M = 'm';
    case YD = 'yd';

    public static function values(): array
    {
        return [
            self::CM->value => 'Cm',
            self::IN->value => 'In',
            self::FT->value => 'Ft',
            self::MM->value => 'Mm',
            self::M->value => 'M',
            self::YD->value => 'Yd',
        ];
    }
}
