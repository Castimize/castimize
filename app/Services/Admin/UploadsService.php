<?php

namespace App\Services\Admin;

use App\Models\Manufacturer;
use App\Models\Upload;

class UploadsService
{
    /**
     * @param Upload $upload
     * @return void
     */
    public function setUploadToOrderQueue(Upload $upload): void
    {
        $orderQueuesService = new OrderQueuesService();
        $manufacturer = Manufacturer::with(['costs'])->orderBy('id')->first();

        $orderQueues = $orderQueuesService->storeFromUpload($upload, [$manufacturer]);

        // Create a order queue status in-queue for all order queues
        foreach ($orderQueues as $orderQueue) {
            $orderQueuesService->setStatus($orderQueue);
        }
    }
}
