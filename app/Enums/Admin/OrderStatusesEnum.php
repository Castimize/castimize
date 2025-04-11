<?php

declare(strict_types=1);

namespace App\Enums\Admin;

enum OrderStatusesEnum: string
{
    case InQueue = 'in-queue';
    case InProduction = 'in-production';
    case AvailableForShipping = 'available-for-shipping';
    case InTransitToDc = 'in-transit-to-dc';
    case AtDc = 'at-dc';
    case InTransitToCustomer = 'in-transit-to-customer';
    case RejectionRequest = 'rejection-request';
    case Reprinted = 'reprinted';
    case Completed = 'completed';
    case Canceled = 'canceled';

    public static function getDcStatuses(): array
    {
        return [
            self::AtDc->value,
            self::InTransitToCustomer->value,
            self::Completed->value,
            self::Canceled->value,
        ];
    }

    public static function getManufacturerStatuses(): array
    {
        return [
            self::InQueue->value,
            self::InProduction->value,
            self::AvailableForShipping->value,
            self::InTransitToDc->value,
            self::RejectionRequest->value,
            self::Reprinted->value,
        ];
    }
}
