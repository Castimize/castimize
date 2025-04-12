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
        return [
            self::EIN->value => self::EIN->value,
            self::VAT->value => self::VAT->value,
            self::IOSS->value => self::IOSS->value,
            self::ARN->value => self::ARN->value,
        ];
    }
}
