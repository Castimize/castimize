<?php

namespace App\Nova\Actions;

use App\Models\OrderQueue;
use App\Services\Admin\OrderQueuesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class PoInProductionStatusAction extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('In production');
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
            $hasEndStatus = [];
            /** @var $model OrderQueue */
            if ($model->getLastStatus()->end_status) {
                $hasEndStatus[] = $model->id;
            }

            if (count($hasEndStatus) > 0) {
                return ActionResponse::danger(
                    __('You selected PO\'s :pos which cannot be changed anymore, because it already has an end status', [
                        'pos' => implode(', ', $hasEndStatus),
                    ])
                );
            }
        }
        foreach ($models as $model) {
            $orderQueuesService->setStatus($model, 'in-production');
            $model->contract_date = now()->addBusinessDays($model->manufacturerCost->shipment_lead_time, 'add')->format('Y-m-d H:i:s');
        }

        return ActionResponse::message(__('PO\'s successfully in production.'));
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
