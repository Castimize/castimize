<?php

namespace App\Nova\Filters;

use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class OrderQueueOrderStatusFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __('Order queue status');
    }

    /**
     * Apply the filter to the given query.
     *
     * @param NovaRequest $request
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->whereHas('orderQueueStatuses', function ($q) use ($value) {
                $q->where('slug', $value)
                    ->whereIn('id', function ($query) {
                        $query
                            ->selectRaw('max(id)')
                            ->from('order_queue_statuses')
                            ->whereColumn('order_queue_id', 'order_queue.id');
                    });
            });
    }

    /**
     * Get the filter's available options.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function options(NovaRequest $request)
    {
        return OrderStatus::all()->pluck('slug', 'status')->toArray();
    }
}
