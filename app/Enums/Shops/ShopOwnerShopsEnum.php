<?php

namespace App\Enums\Shops;

enum ShopOwnerShopsEnum: string
{
    case Etsy = 'etsy';

    public static function getList(): array
    {
        return [
            self::Etsy->value => self::Etsy->name,
        ];
    }
}
