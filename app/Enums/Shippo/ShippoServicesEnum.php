<?php

namespace App\Enums\Shippo;

enum ShippoServicesEnum: string
{
    case UPS_STANDARD = 'ups_standard';
    case UPS_SAVER = 'ups_saver';
    case UPS_EXPRESS_SAVER_WORLDWIDE_CA = 'ups_express_saver_worldwide_ca';
    case FEDEX_GROUND = 'fedex_ground';
    case FEDEX_HOME_DELIVERY = 'fedex_home_delivery';
    case FEDEX_EXPRESS_SAVER = 'fedex_express_saver';
    case FEDEX_STANDARD_OVERNIGHT = 'fedex_standard_overnight';
    case FEDEX_PRIORITY_OVERNIGHT = 'fedex_priority_overnight';
    case FEDEX_FIRST_OVERNIGHT = 'fedex_first_overnight';
    case FEDEX_REGIONAL_ECONOMY = 'fedex_regional_economy';
    case FEDEX_INTERNATIONAL_ECONOMY = 'fedex_international_economy';
    case FEDEX_INTERNATIONAL_PRIORITY = 'fedex_international_priority';
    case FEDEX_INTERNATIONAL_FIRST = 'fedex_international_first';

    public static function values(): array
    {
        return [
            self::UPS_STANDARD->value => 'UPS Standard℠',
            self::UPS_SAVER->value => 'UPS Express Saver',
            self::UPS_EXPRESS_SAVER_WORLDWIDE_CA->value => 'UPS Worldwide Express Saver®',
            self::FEDEX_GROUND->value => 'FedEx Ground®',
            self::FEDEX_HOME_DELIVERY->value => 'FedEx Home Delivery®',
            self::FEDEX_EXPRESS_SAVER->value => 'FedEx Express Saver®',
            self::FEDEX_STANDARD_OVERNIGHT->value => 'FedEx Standard Overnight®',
            self::FEDEX_PRIORITY_OVERNIGHT->value => 'FedEx Priority Overnight®',
            self::FEDEX_FIRST_OVERNIGHT->value => 'FedEx First Overnight®',
            self::FEDEX_REGIONAL_ECONOMY->value => 'FedEx Regional Economy®',
            self::FEDEX_INTERNATIONAL_ECONOMY->value => 'FedEx International Economy®',
            self::FEDEX_INTERNATIONAL_PRIORITY->value => 'FedEx International Priority®',
            self::FEDEX_INTERNATIONAL_FIRST->value => 'FedEx International First®',
        ];
    }
}
