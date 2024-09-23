<?php

namespace App\Nova;

use App\Nova\Filters\ShowDeleted;
use App\Traits\Nova\CommonMetaDataTrait;
use DigitalCreative\ColumnToggler\ColumnTogglerTrait;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class ShippingFee extends Resource
{
    use ColumnTogglerTrait, CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ShippingFee>
     */
    public static $model = \App\Models\ShippingFee::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'currency_code',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'name' => 'asc',
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

            Text::make(__('Name'), 'name')
                ->sortable(),

            BelongsTo::make(__('Logistics zone'), 'logisticsZone')
                ->sortable(),

            Select::make(__('Currency'), 'currency_code')->options(function () {
                return array_filter(\App\Models\Currency::pluck('name', 'code')->all());
            })
                ->hideFromIndex()
                ->help(__('This currency will be used for below price')),

            \Laravel\Nova\Fields\Currency::make(__('Default rate'), 'default_rate')
                ->min(0)
                ->step(0.01)
                ->locale(config('app.format_locale'))
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                )
                ->sortable(),

            Number::make(__('Default lead time'), 'default_lead_time')
                ->step(1),

            Number::make(__('Cc threshold 1'), 'cc_threshold_1')
                ->hideByDefault()
                ->step(0.01),

            Number::make(__('Rate increase 1'), 'rate_increase_1')
                ->hideByDefault()
                ->step(0.01)
                ->displayUsing(function ($value) {
                    return $value . '%';
                }),

            Number::make(__('Cc threshold 2'), 'cc_threshold_2')
                ->hideByDefault()
                ->step(0.01),

            Number::make(__('Rate increase 2'), 'rate_increase_2')
                ->hideByDefault()
                ->step(0.01)
                ->displayUsing(function ($value) {
                    return $value . '%';
                }),

            Number::make(__('Cc threshold 3'), 'cc_threshold_3')
                ->hideByDefault()
                ->step(0.01),

            Number::make(__('Rate increase 3'), 'rate_increase_3')
                ->hideByDefault()
                ->step(0.01)
                ->displayUsing(function ($value) {
                    return $value . '%';
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
