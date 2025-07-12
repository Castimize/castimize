<?php

namespace App\Observers;

use App\Enums\Admin\CurrencyEnum;
use App\Models\Currency;
use App\Models\PaymentFee;

class PaymentFeeObserver
{
    /**
     * Handle the PaymentFee "creating" event.
     */
    public function creating(PaymentFee $paymentFee): void
    {
        if (! $paymentFee->currency_code) {
            $paymentFee->currency_code = CurrencyEnum::USD->value;
        }
        if ($paymentFee->currency_code && $paymentFee->currency === null) {
            $currency = Currency::where('code', $paymentFee->currency_code)->first();
            if ($currency) {
                $paymentFee->currency_id = $currency->id;
            }
        }
    }

    /**
     * Handle the PaymentFee "updating" event.
     */
    public function updating(PaymentFee $paymentFee): void
    {
        if (! $paymentFee->currency_code) {
            $paymentFee->currency_code = CurrencyEnum::USD->value;
        }
        if ($paymentFee->currency_code && $paymentFee->currency === null) {
            $currency = Currency::where('code', $paymentFee->currency_code)->first();
            if ($currency) {
                $paymentFee->currency_id = $currency->id;
            }
        }
    }
}
