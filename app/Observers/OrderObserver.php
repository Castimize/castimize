<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     */
    public function creating(Order $order): void
    {
        if ($order->currency_code && $order->currency === null) {
            $currency = Currency::where('code', $order->currency_code)->first();
            if ($currency) {
                $order->currency_id = $currency->id;
            }
        }
    }

    /**
     * Handle the Order "updating" event.
     */
    public function updating(Order $order): void
    {
        if ($order->currency_code && $order->currency === null) {
            $currency = Currency::where('code', $order->currency_code)->first();
            if ($currency) {
                $order->currency_id = $currency->id;
            }
        }
    }
}
