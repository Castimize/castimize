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
        return [
            self::Apartment->value => self::Apartment->name,
            self::Building->value => self::Building->name,
            self::Department->value => self::Department->name,
            self::Floor->value => self::Floor->name,
            self::Room->value => self::Room->name,
            self::Suite->value => self::Suite->name,
        ];
    }
}
