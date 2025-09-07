<?php

namespace App\Jobs;

use App\Services\Admin\ModelsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StoreModelFromApi implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $request)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new ModelsService)->storeModelFromApi($this->request);
    }
}
