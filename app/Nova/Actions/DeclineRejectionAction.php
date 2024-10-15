<?php

namespace App\Nova\Actions;

use App\Models\OrderQueueStatus;
use App\Models\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class DeclineRejectionAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Decline rejection');
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // New line in order queue status with slug in_production
        foreach ($models as $model) {
            $orderStatus = OrderStatus::where('slug', 'in-production')->first();
            OrderQueueStatus::create([
                'order_queue_id' => $model->order_queue_id,
                'order_status_id' => $orderStatus->id,
                'status' => $orderStatus->status,
            ]);

            $model->declined_at = now();
            $model->save();

            return ActionResponse::message(__('Rejection declined and put back in queue on status in-production'));
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
