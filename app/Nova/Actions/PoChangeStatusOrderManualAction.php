<?php

namespace App\Nova\Actions;

use App\Models\OrderStatus;
use App\Services\Admin\OrderQueuesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class PoChangeStatusOrderManualAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Change status manual');
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
        $orderQueuesService = new OrderQueuesService();
        foreach ($models as $model) {
            $orderQueuesService->setStatus($model, $fields->order_status);
            // Set status manual changed to order queue
            $model->status_manual_changed = true;
            $model->save();
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
        return [
            Heading::make('<div class="text-secondary">' . __('NOTE! This will override the automatic flow') . '</div>')
                ->textAlign('center')
                ->asHtml(),
            Select::make(__('Status'),'order_status')
                ->options(
                    OrderStatus::all()->pluck('status', 'slug')->toArray()
                )->displayUsingLabels(),
        ];
    }
}
