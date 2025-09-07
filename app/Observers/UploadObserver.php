<?php

namespace App\Observers;

use App\Models\Upload;
use App\Services\Admin\OrderQueuesService;

class UploadObserver
{
    /**
     * Handle the Upload "creating" event.
     */
    public function creating(Upload $upload): void {}

    /**
     * Handle the Upload "updated" event.
     */
    public function updated(Upload $upload): void
    {
        if ($upload->isDirty('manufacturer_discount')) {
            app(OrderQueuesService::class)->recalculateManufacturerCosts($upload);
        }
    }
}
