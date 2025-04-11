<?php

namespace App\Enums\Etsy;

enum EtsyListingStatesEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case SoldOut = 'sold_out';
    case Draft = 'draft';
    case Expired = 'expired';
}
