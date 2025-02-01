<?php

namespace App\Console\Commands\Temp;

use App\Jobs\SyncExchangeRateToExact;
use App\Models\CurrencyHistoryRate;
use App\Services\Exact\ExactOnlineService;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncHistoryExchangeRatesToExact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:sync-history-exchange-rates-to-exact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to sync history exchange rates to Exact';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateFrom = Carbon::parse('2025-01-07');
        $dateTo = now();
        $period = CarbonPeriod::create($dateFrom, $dateTo);

        $baseCurrency = config('app.currency');
        $supportedCurrencies = config('app.supported_currencies');

        try {
            $exchangeRates = app(ExchangeRate::class);

            $count = count($period);
            $progressBar = $this->output->createProgressBar($count);
            $this->info("Syncing $count exchange rates to Exact");
            $progressBar->start();

            foreach ($period as $date) {
//                $result = $exchangeRates->exchangeRateBetweenDateRange(
//                    $baseCurrency,
//                    $supportedCurrencies,
//                    $date,
//                    $date
//                );

                //foreach ($result[$date->format('Y-m-d')] as $convertCurrency => $rate) {
                    $currencyHistoryRate = CurrencyHistoryRate::where('convert_currency', 'EUR')
                        ->where('historical_date', $date->format('Y-m-d'))
                        ->first();

                    if (!$currencyHistoryRate) {
                        $result = $exchangeRates->exchangeRateBetweenDateRange(
                            $baseCurrency,
                            $supportedCurrencies,
                            $date,
                            $date
                        );

                        foreach ($result[$date->format('Y-m-d')] as $convertCurrency => $rate) {
                            $currencyHistoryRate = CurrencyHistoryRate::create([
                                'base_currency' => $baseCurrency,
                                'convert_currency' => $convertCurrency,
                                'rate' => $rate,
                                'historical_date' => $date->format('Y-m-d'),
                            ]);
                        }
                    }
                    if ($currencyHistoryRate && $currencyHistoryRate->convert_currency === 'EUR' && $currencyHistoryRate->exact_online_guid === null) {
//                        $exchangeRate = (new ExactOnlineService())->syncExchangeRate($currencyHistoryRate);
                        //dd($exchangeRate);
                        SyncExchangeRateToExact::dispatch($currencyHistoryRate);
                    }
                //}
                sleep(1);
                $progressBar->advance();
            }
            $progressBar->finish();
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}
