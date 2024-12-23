<?php

namespace App\Jobs;

use App\Models\CurrencyHistoryRate;
use App\Services\Admin\LogRequestService;
use App\Services\Exact\ExactOnlineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncExchangeRateToExact implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(private CurrencyHistoryRate $currencyHistoryRate, public ?int $logRequestId = null)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ExactOnlineService $exactOnlineService): void
    {
        $exchangeRate = null;

        try {
            $exchangeRate = $exactOnlineService->syncExchangeRate($this->currencyHistoryRate);
        } catch (Throwable $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $exchangeRate);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }
}
