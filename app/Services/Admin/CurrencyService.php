<?php

namespace App\Services\Admin;

use App\Models\CurrencyHistoryRate;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    private ExchangeRate $exchangeRate;

    /**
     * Cache for preloaded currency rates.
     * Format: ['EUR_USD_2025-01-15' => CurrencyHistoryRate, ...]
     */
    private array $preloadedRates = [];

    /**
     * Most recent rate per currency pair (fallback).
     * Format: ['EUR_USD' => CurrencyHistoryRate, ...]
     */
    private array $mostRecentRates = [];

    public function __construct()
    {
        $this->exchangeRate = app(ExchangeRate::class);
    }

    /**
     * Preload currency rates for a set of currencies and dates in a single query.
     * This eliminates N+1 queries when converting multiple amounts.
     *
     * @param  array  $currencies  Array of currency codes to preload (e.g., ['USD', 'GBP'])
     * @param  Carbon|null  $date  Single date to preload rates for
     * @param  array|Collection|null  $dates  Multiple dates to preload rates for
     */
    public function preloadRates(array $currencies, ?Carbon $date = null, array|Collection|null $dates = null): void
    {
        if (empty($currencies)) {
            return;
        }

        $baseCurrency = config('app.currency');

        // Build date list
        $dateList = [];
        if ($date !== null) {
            $dateList[] = $date->format('Y-m-d');
        }
        if ($dates !== null) {
            foreach ($dates as $d) {
                $dateList[] = $d instanceof Carbon ? $d->format('Y-m-d') : $d;
            }
        }
        $dateList = array_unique($dateList);

        // Query all needed rates at once
        $query = CurrencyHistoryRate::where('base_currency', $baseCurrency)
            ->whereIn('convert_currency', $currencies);

        if (! empty($dateList)) {
            $query->whereIn('historical_date', $dateList);
        }

        $rates = $query->get();

        // Cache the rates
        foreach ($rates as $rate) {
            $key = $this->getCacheKey($rate->base_currency, $rate->convert_currency, $rate->historical_date->format('Y-m-d'));
            $this->preloadedRates[$key] = $rate;
        }

        // Also preload the most recent rates as fallback
        $mostRecentRates = CurrencyHistoryRate::where('base_currency', $baseCurrency)
            ->whereIn('convert_currency', $currencies)
            ->orderBy('historical_date', 'desc')
            ->get()
            ->unique('convert_currency');

        foreach ($mostRecentRates as $rate) {
            $pairKey = $rate->base_currency.'_'.$rate->convert_currency;
            $this->mostRecentRates[$pairKey] = $rate;
        }
    }

    /**
     * Clear all preloaded rates from cache.
     */
    public function clearPreloadedRates(): void
    {
        $this->preloadedRates = [];
        $this->mostRecentRates = [];
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

        $dateStr = $date->format('Y-m-d');

        // Check preloaded cache first
        $cacheKey = $this->getCacheKey($baseCurrency, $convertCurrency, $dateStr);
        if (isset($this->preloadedRates[$cacheKey])) {
            return $this->calculateRate($this->preloadedRates[$cacheKey], $from, $amount);
        }

        // Check most recent rates cache (fallback)
        $pairKey = $baseCurrency.'_'.$convertCurrency;
        if (isset($this->mostRecentRates[$pairKey])) {
            return $this->calculateRate($this->mostRecentRates[$pairKey], $from, $amount);
        }

        // Fall back to database query if not preloaded
        $currencyHistoricalRate = CurrencyHistoryRate::where('base_currency', $baseCurrency)
            ->where('convert_currency', $convertCurrency)
            ->where('historical_date', $dateStr)
            ->first();

        if ($currencyHistoricalRate) {
            return $this->calculateRate($currencyHistoricalRate, $from, $amount);
        }

        Log::info(sprintf('From: %s, To: %s, Amount: %s, Date: %s', $from, $to, $amount, $dateStr));

        $currencyHistoricalRate = CurrencyHistoryRate::where('base_currency', $baseCurrency)
            ->where('convert_currency', $convertCurrency)
            ->orderBy('historical_date', 'desc')
            ->first();
        if ($currencyHistoricalRate) {
            return $this->calculateRate($currencyHistoricalRate, $from, $amount);
        }

        return $amount;
    }

    private function getCacheKey(string $baseCurrency, string $convertCurrency, string $date): string
    {
        return $baseCurrency.'_'.$convertCurrency.'_'.$date;
    }

    private function calculateRate($currencyHistoricalRate, string $from, float $amount): float
    {
        if ($currencyHistoricalRate->convert_currency === $from) {
            return $amount / $currencyHistoricalRate->rate;
        }

        return $amount * $currencyHistoricalRate->rate;
    }
}
