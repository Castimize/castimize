<?php

declare(strict_types=1);

namespace App\Enums\Admin;

enum ShippingServiceLevelTokenEnum: string
{
    case UpsStandard = 'ups_standard';
    case UpsSaver = 'ups_saver';
}
