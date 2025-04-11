<?php

namespace App\Enums\Woocommerce;

enum WcOrderStatesEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case OnHold = 'on-hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case Failed = 'failed';
    case Trash = 'trash';
}
