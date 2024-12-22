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
    public function __construct(public int $currencyHistoryRateId, public ?int $logRequestId = null)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ExactOnlineService $exactOnlineService): void
    {
        $currencyHistoryRate = CurrencyHistoryRate::find($this->currencyHistoryRateId);
        $exchangeRate = null;

        if ($currencyHistoryRate === null) {
            return;
        }

        try {
            $exchangeRate = $exactOnlineService->syncExchangeRate($currencyHistoryRate);
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
