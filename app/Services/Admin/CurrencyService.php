<?php

namespace App\Services\Admin;

use App\Models\CurrencyHistoryRate;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    private $exchangeRate;

    public function __construct()
    {
        $this->exchangeRate = app(ExchangeRate::class);
    }

    public function convertCurrency(string $from, string $to, float $amount, ?Carbon $date = null): float
    {
        if ($from === $to) {
            return $amount;
        }
        if ($date === null) {
            $date = now();
        }
        $baseCurrency = $from;
        $convertCurrency = $to;
        if ($to === config('app.currency')) {
            $baseCurrency = $to;
            $convertCurrency = $from;
        }

        $currencyHistoricalRate = CurrencyHistoryRate::where('base_currency', $baseCurrency)
            ->where('convert_currency', $convertCurrency)
            ->where('historical_date', $date->format('Y-m-d'))
            ->first();

        if ($currencyHistoricalRate) {
            if ($currencyHistoricalRate->convert_currency === $from) {
                return $amount / $currencyHistoricalRate->rate;
            }
            return $amount * $currencyHistoricalRate->rate;
        }

        Log::info(sprintf('From: %s, To: %s, Amount: %s, Date: %s', $from, $to, $amount, $date->format('Y-m-d')));

        return $this->exchangeRate->convert($amount, $from, $to, $date);
    }
}
