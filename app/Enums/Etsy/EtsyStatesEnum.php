<?php

namespace App\Enums\Etsy;

enum EtsyStatesEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case SoldOut = 'sold_out';
    case Draft = 'draft';
    case Expired = 'expired';
}
