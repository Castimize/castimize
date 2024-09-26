<?php

namespace App\Jobs;

use App\Models\Manufacturer;
use App\Models\Upload;
use App\Services\Admin\OrderQueuesService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadToOrderQueue implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private Upload $upload)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $orderQueuesService = new OrderQueuesService();
        $manufacturer = Manufacturer::with(['costs'])->orderBy('id')->first();
        $orderQueues = $orderQueuesService->storeFromUpload($this->upload, [$manufacturer]);

        // Create a order queue status in-queue for all order queues
        foreach ($orderQueues as $orderQueue) {
            $orderQueuesService->setStatus($orderQueue);
        }
    }
}
