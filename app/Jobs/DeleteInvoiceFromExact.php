<?php

namespace App\Jobs;

use App\Services\Exact\ExactOnlineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteInvoiceFromExact implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 120;

    public function __construct(
        public readonly string $yourRef,
    ) {}

    public function handle(ExactOnlineService $exactOnlineService): void
    {
        Log::channel('exact')->info('DeleteInvoiceFromExact: dispatched', [
            'your_ref' => $this->yourRef,
        ]);

        try {
            $exactOnlineService->deleteByYourRef($this->yourRef);
        } catch (Throwable $e) {
            Log::channel('exact')->error('DeleteInvoiceFromExact: failed', [
                'your_ref' => $this->yourRef,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
