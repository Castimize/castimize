<?php

namespace App\Console\Commands;

use App\Models\CurrencyHistoryRate;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetCurrencyHistoricalRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:get-currency-historical-rates';

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
        try {
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
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}
