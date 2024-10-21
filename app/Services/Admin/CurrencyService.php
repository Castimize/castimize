<?php

namespace App\Services\Admin;

use App\Models\CurrencyHistoryRate;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;

class CurrencyService
{
    private $exchangeRate;

    public function __construct()
    {
        $this->exchangeRate = app(ExchangeRate::class);
    }

    /**
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return float
     */
    public function convertCurrency(string $from, string $to, float $amount): float
    {
        if ($from === $to) {
            return $amount;
        }

        $currencyHistoricalRate = CurrencyHistoryRate::where('base_currency', $from)
            ->where('convert_currency', $to)
            ->where('historical_date', now()->format('Y-m-d'))
            ->first();

        if ($currencyHistoricalRate) {
            if ($currencyHistoricalRate->convert_currency === $from) {
                return $amount * $currencyHistoricalRate->rate;
            }
            return $amount / $currencyHistoricalRate->rate;
        }

        return $this->exchangeRate->convert($amount, $from, $to, now());
    }
}
