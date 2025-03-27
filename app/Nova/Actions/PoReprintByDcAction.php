<?php

namespace App\Nova\Actions;

use App\Models\OrderQueue;
use App\Models\ReprintCulprit;
use App\Models\ReprintReason;
use App\Services\Admin\OrderQueuesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class PoReprintByDcAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Reprint');
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
            if ($model->getLastStatus()->end_status && $model->getLastStatus()->slug !== 'completed') {
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
            /**
             * @var $model OrderQueue
             */
            $model->reprint()->create([
                'manufacturer_id' => $model->manufacturer_id,
                'order_id' => $model->upload->order_id,
                'reprint_culprit_id' => $fields->reprint_culprit_id,
                'reprint_reason_id' => $fields->reprint_reason_id,
                'reason' => $fields->reason,
            ]);

            $orderQueuesService->setStatus($model, 'reprinted');

            $newOrderQueue = OrderQueue::create([
                'manufacturer_id' => $model->manufacturer_id,
                'upload_id' => $model->upload_id,
                'order_id' => $model->upload->order_id,
                'shipping_fee_id' => $model->shipping_fee_id,
                'manufacturer_shipment_id' => null,
                'manufacturer_cost_id' => $model->manufacturer_cost_id,
                'customer_shipment_id' => null,
                'due_date' => $model->due_date,
                'final_arrival_date' => $model->final_arrival_date,
                'manufacturer_costs' => $model->manufacturer_costs,
                'currency_code' => $model->currency_code,
            ]);
            $orderQueuesService->setStatus($newOrderQueue, 'in-queue');
        }

        return ActionResponse::message(__('Successfully created reprint for selected PO\'s.'));
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
            Select::make(__('Reprint culprit'),'reprint_culprit_id')
                ->options(
                    ReprintCulprit::all()->pluck('culprit', 'id')->toArray()
                )->displayUsingLabels(),


            Select::make(__('Reprint reason'),'reprint_reason_id')
                ->options(
                    ReprintReason::all()->pluck('reason', 'id')->toArray()
                )->displayUsingLabels(),

            Textarea::make(__('Reason'),'reason'),
        ];
    }
}
