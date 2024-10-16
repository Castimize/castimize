<?php

namespace App\Console\Commands;

use App\Models\CurrencyHistoryRate;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Illuminate\Console\Command;

class GetCurrencyHistoricalRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-currency-historical-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get currency historical rates for a day and save to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseCurrency = config('app.currency');
        $supportedCurrencies = config('app.supported_currencies');
        $exchangeRates = app(ExchangeRate::class);

        $result = $exchangeRates->exchangeRate($baseCurrency, $supportedCurrencies);

        foreach ($result as $convertCurrency => $rate) {
            CurrencyHistoryRate::create([
                'base_currency' => $baseCurrency,
                'convert_currency' => $convertCurrency,
                'rate' => $rate,
                'historical_date' => now()->format('Y-m-d'),
            ]);
        }

    }
}
