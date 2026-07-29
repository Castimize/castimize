<?php

declare(strict_types=1);

namespace App\Enums\Admin;

enum ShippingServiceLevelTokenEnum: string
{
    case UpsStandard = 'ups_standard';
    case UpsSaver = 'ups_saver';
    case FedexGround = 'fedex_ground';
    case FedexHomeDelivery = 'fedex_home_delivery';
    case FedexExpressSaver = 'fedex_express_saver';
    case FedexStandardOvernight = 'fedex_standard_overnight';
    case FedexPriorityOvernight = 'fedex_priority_overnight';
    case FedexFirstOvernight = 'fedex_first_overnight';
    case FedexRegionalEconomy = 'fedex_regional_economy';
    case FedexInternationalEconomy = 'fedex_international_economy';
    case FedexInternationalPriority = 'fedex_international_priority';
    case FedexInternationalFirst = 'fedex_international_first';
}
