<?php

namespace App\Nova\Actions;

use App\Services\Admin\OrderQueuesService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class DeclineRejectionAction extends Action
{
    use InteractsWithQueue;
    use Queueable;

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
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $orderQueuesService = new OrderQueuesService;
        // New line in order queue status with slug in_production
        foreach ($models as $model) {
            $model->declined_at = now();
            $model->save();

            $orderQueuesService->setStatus($model->orderQueue);
        }

        return ActionResponse::message(__('Selected rejections declined and PO put back in queue on status in-production'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
