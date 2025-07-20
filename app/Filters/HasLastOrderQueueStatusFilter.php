<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class HasLastOrderQueueStatusFilter
{
    public function __construct(
        private string $statusSlug,
    ) {
    }

    public function __invoke(Builder $query): void
    {
        $query->whereHas('orderQueueStatuses', function ($q) {
            $q->where('slug', $this->statusSlug)
                ->whereIn('id', function ($query) {
                    $query
                        ->selectRaw('max(id)')
                        ->from('order_queue_statuses')
                        ->whereColumn('order_queue_id', 'order_queue.id');
                });
        });
    }
}
