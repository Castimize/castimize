<?php

namespace App\Enums\Shippo;

enum ShippoBuildingTypesEnum: string
{
    case Apartment = 'apartment';
    case Building = 'building';
    case Department = 'department';
    case Floor = 'floor';
    case Room = 'room';
    case Suite = 'suite';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
