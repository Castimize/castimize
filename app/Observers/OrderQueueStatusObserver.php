<?php

namespace App\Observers;

use App\Models\OrderQueueStatus;
use App\Models\OrderStatus;

class OrderQueueStatusObserver
{
    /**
     * Handle the OrderQueueStatus "creating" event.
     */
    public function creating(OrderQueueStatus $orderQueueStatus): void
    {
        if (empty($orderQueueStatus->status)) {
            $orderStatus = OrderStatus::find($orderQueueStatus->order_queue_status_id);
            if ($orderStatus) {
                $orderQueueStatus->status = $orderStatus->status;
                $orderQueueStatus->slug = $orderStatus->slug;
            }
        }
    }
}
