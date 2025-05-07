<?php

namespace App\Enums\Shippo;

enum ShippoLabelFileTypesEnum: string
{
    case PNG = 'PNG';
    case PNG_2_3X7_5 = 'PNG_2.3x7.5';
    case PDF = 'PDF';
    case PDF_2_3X7_5 = 'PDF_2.3x7.5';
    case PDF_4X6 = 'PDF_4x6';
    case PDF_4X8 = 'PDF_4x8';
    case PDF_A4 = 'PDF_A4';
    case PDF_A5 = 'PDF_A5';
    case PDF_A6 = 'PDF_A6';
    case ZPLII = 'ZPLII';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
