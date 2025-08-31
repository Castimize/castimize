<?php

namespace App\Enums\Shippo;

enum ShippoCustomsDeclarationIncoTermsEnum: string
{
    case DDP = 'DDP';
    case DDU = 'DDU';
    case FCA = 'FCA';
    case DAP = 'DAP';
    case EDAP = 'eDAP';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
