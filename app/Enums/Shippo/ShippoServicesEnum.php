<?php

namespace App\Enums\Shippo;

enum ShippoServicesEnum: string
{
    case UPS_STANDARD = 'ups_standard';
    case UPS_SAVER = 'ups_saver';
    case UPS_EXPRESS_SAVER_WORLDWIDE_CA = 'ups_express_saver_worldwide_ca';

    public static function values(): array
    {
        return [
            self::UPS_STANDARD->value => 'UPS Standard℠',
            self::UPS_SAVER->value => 'UPS Express Saver',
            self::UPS_EXPRESS_SAVER_WORLDWIDE_CA->value => 'UPS Worldwide Express Saver®',
        ];
    }
}
