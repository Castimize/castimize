<?php

namespace App\Enums\Shippo;

enum ShippoOrderStatusesEnum: string
{
    case UNKNOWN = 'UNKNOWN';
    case AWAIT_PAY = 'AWAITPAY';
    case PAID = 'PAID';
    case REFUNDED = 'REFUNDED';
    case CANCELLED = 'CANCELLED';
    case PARTIALLY_FULFILLED = 'PARTIALLY_FULFILLED';
    case SHIPPED = 'SHIPPED';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
