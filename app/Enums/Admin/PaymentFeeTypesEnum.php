<?php

namespace App\Enums\Admin;

use Laravel\Nova\Nova;

enum PaymentFeeTypesEnum: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';

    public static function options(): array
    {
        return [
            self::FIXED->value => (string) Nova::__('Fixed'),
            self::PERCENTAGE->value => (string) Nova::__('Percentage'),
        ];
    }
}
