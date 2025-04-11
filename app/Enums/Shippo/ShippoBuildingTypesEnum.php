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
}
