<?php

namespace App\Nova\Actions;

use App\Services\Admin\OrderQueuesService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;

class ShipmentInTransitToCustomerStatusAction extends Action
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
        return __('In transit to customer');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $orderQueuesService = new OrderQueuesService;
        foreach ($models as $model) {
            $model->sent_at = $fields->sent_at ?? now();
            foreach ($model->orderQueues as $orderQueue) {
                $orderQueuesService->setStatus($orderQueue, 'in-transit-to-customer');
            }
        }

        return ActionResponse::message(__('PO\'s successfully in transit to customer.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            DateTime::make(__('Date sent at'), 'sent_at')
                ->help(__('Leave empty to set sent_at at now()')),
        ];
    }
}
