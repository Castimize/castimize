<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class PoSetManufacturerDiscountAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // Order Queue
        foreach ($models as $model) {
            $model->manufacturer_discount = $fields->manufacturer_discount;
            $model->save();
        }

        return ActionResponse::message(__('Successfully set manufacturer discount and recalculated manufacturer costs for selected PO\'s.'));
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
            Number::make(__('Manufacturer discount'), 'manufacturer_discount')
                ->help(__('In percentage'))
                ->onlyOnForms()
                ->step(0.01),
        ];
    }
}
