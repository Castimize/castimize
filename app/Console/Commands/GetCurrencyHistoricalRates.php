<?php

namespace App\Console\Commands;

use App\Jobs\SyncExchangeRateToExact;
use App\Models\CurrencyHistoryRate;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
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
    protected $signature = 'castimize:get-currency-historical-rates {--historical-date=}';

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

            if ($this->option('historical-date')) {
                $historicalDate = Carbon::parse($this->option('historical-date'));
                $result = $exchangeRates->exchangeRate(
                    from: $baseCurrency,
                    to: $supportedCurrencies,
                    date: $historicalDate,
                );
            } else {
                $historicalDate = Carbon::now();
                $result = $exchangeRates->exchangeRate(
                    from: $baseCurrency,
                    to: $supportedCurrencies,
                );
            }

            foreach ($result as $convertCurrency => $rate) {
                $currencyHistoryRate = CurrencyHistoryRate::create([
                    'base_currency' => $baseCurrency,
                    'convert_currency' => $convertCurrency,
                    'rate' => $rate,
                    'historical_date' => $historicalDate->format('Y-m-d'),
                ]);
                if ($currencyHistoryRate->convert_currency === 'EUR') {
                    SyncExchangeRateToExact::dispatch($currencyHistoryRate)->onQueue('exact');
                }
            }
        } catch (InvalidFormatException|Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $this->error($e->getMessage());
        }
    }
}
