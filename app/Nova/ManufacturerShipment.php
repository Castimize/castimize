<?php

namespace App\Nova;

use App\Nova\Filters\ShowDeleted;
use App\Traits\Nova\CommonMetaDataTrait;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class ManufacturerShipment extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ManufacturerShipment>
     */
    public static $model = \App\Models\ManufacturerShipment::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'id' => 'desc',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            DateTime::make(__('Sent at'), 'sent_at')
                ->sortable(),

            DateTime::make(__('Arrived at'), 'arrived_at')
                ->sortable(),

            Number::make(__('Time in transit'), 'time_in_transit')->exceptOnForms(),

            DateTime::make(__('Expected delivery date'), 'expected_delivery_date')
                ->sortable(),

            Text::make(__('Type'), 'type'),

            Text::make(__('UPS  tracking'), 'ups_tracking'),

            Textarea::make(__('UPS tracking manual'), 'ups_tracking_manual')
                ->hideFromIndex(),

            Number::make(__('Total parts'), 'total_parts')
                ->min(1)
                ->step(1),

            Select::make(__('Currency'), 'currency_code')->options(function () {
                return array_filter(\App\Models\Currency::pluck('name', 'code')->all());
            })
                ->hideFromIndex()
                ->help(__('This currency will be used for all below prices')),

            \Laravel\Nova\Fields\Currency::make(__('Total costs'), 'total_costs')
                ->min(0)
                ->step(0.01)
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                )
                ->displayUsing(function ($value) {
                    if ($value !== null) {
                        return currencyFormatter($value, $this->currency_code);
                    }
                    return $value;
                }),

            new Panel(__('History'), $this->commonMetaData(false, false, false, false)),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new ShowDeleted(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
