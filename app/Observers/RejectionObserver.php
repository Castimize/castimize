<?php

namespace App\Observers;

use App\Models\OrderStatus;
use App\Models\Rejection;
use App\Models\RejectionReason;

class RejectionObserver
{
    /**
     * Handle the Rejection "creating" event.
     */
    public function creating(Rejection $rejection): void
    {
        $rejectionReason = RejectionReason::find($rejection->rejection_reason_id);
        if ($rejectionReason) {
            $rejection->reason_manufacturer = $rejectionReason->reason;
        }
    }

    /**
     * Handle the Rejection "updating" event.
     */
    public function updating(Rejection $rejection): void
    {
        if ($rejection->isDirty('rejection_reason_id')) {
            $rejectionReason = RejectionReason::find($rejection->rejection_reason_id);
            if ($rejectionReason) {
                $rejection->reason_manufacturer = $rejectionReason->reason;
            }
        }
    }
}
