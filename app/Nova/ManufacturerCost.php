<?php

namespace App\Nova;

use App\Nova\Filters\ShowDeleted;
use App\Traits\Nova\CommonMetaDataTrait;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Vyuldashev\NovaMoneyField\Money;

class ManufacturerCost extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ManufacturerCost>
     */
    public static $model = \App\Models\ManufacturerCost::class;

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

            BelongsTo::make(__('Material'), 'material', Material::class)
                ->sortable(),

            Select::make(__('Currency'), 'currency_code')->options(function () {
                return array_filter(\App\Models\Currency::pluck('name', 'code')->all());
            })
                ->hideFromIndex()
                ->help(__('This currency will be used for all below prices')),

            Boolean::make(__('Active'), 'active')
                ->sortable(),

            Boolean::make(__('Setup fee'), 'setup_fee')
                ->sortable(),

            \Laravel\Nova\Fields\Currency::make(__('Setup fee amount'), 'setup_fee_amount')
                ->onlyOnForms()
                ->min(0)
                ->step(0.01)
                ->locale(config('app.format_locale'))
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                ),


            Number::make(__('Production lead time'), 'production_lead_time')
                ->min(0)
                ->step(1)
                ->sortable(),

            Number::make(__('Shipment lead time'), 'shipment_lead_time')
                ->min(0)
                ->step(1)
                ->sortable(),

            \Laravel\Nova\Fields\Currency::make(__('Costs minimum per stl'), 'costs_minimum_per_stl')
                ->onlyOnForms()
                ->min(0)
                ->step(0.01)
                ->locale(config('app.format_locale'))
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                ),

            \Laravel\Nova\Fields\Currency::make(__('Costs volume cc'), 'costs_volume_cc')
                ->min(0)
                ->step(0.01)
                ->locale(config('app.format_locale'))
                ->hideFromIndex()
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                ),

            \Laravel\Nova\Fields\Currency::make(__('Costs surface cm2'), 'costs_surface_cm2')
                ->min(0)
                ->step(0.01)
                ->locale(config('app.format_locale'))
                ->hideFromIndex()
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                ),

            new Panel(__('History'), $this->commonMetaData(true, false, false, false)),
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
