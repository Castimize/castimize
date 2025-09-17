<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Services\Admin\UploadsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UploadToOrderQueue implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Upload $upload,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(UploadsService $uploadsService): void
    {
        $uploadsService->setUploadToOrderQueue($this->upload);
    }
}
