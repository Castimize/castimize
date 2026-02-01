<?php

namespace App\Nova\Actions;

use App\Models\OrderQueue;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\OrderQueuesService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class PoInProductionStatusAction extends Action
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
        return __('Accept');
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
            if (! $model->manufacturerCost) {
                $manufacturerCost = auth()
                    ->user()
                    ->manufacturer
                    ->costs
                    ->where('active', true)
                    ->where('material_id', $model->upload->material_id)
                    ->first();
                if ($manufacturerCost) {
                    $model->manufacturer_cost_id = $manufacturerCost->id;
                    $model->manufacturer_costs = (new CalculatePricesService)->calculateCostsOfModel(
                        $manufacturerCost,
                        $model->upload->model_volume_cc,
                        $model->upload->model_surface_area_cm2,
                        $model->upload->quantity
                    );
                    $model->save();
                    $model->load('manufacturerCost');
                }
            }
            $model->contract_date = now()->addBusinessDays($model->manufacturerCost->shipment_lead_time)->format('Y-m-d H:i:s');
            $model->save();
            $orderQueuesService->setStatus($model, 'in-production');
        }

        return ActionResponse::visit('/resources/pos/lens/in-production');
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
