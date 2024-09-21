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

class Price extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Price>
     */
    public static $model = \App\Models\Price::class;

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
        'id' => 'asc',
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

            BelongsTo::make(__('Country'), 'country', Country::class),

            Select::make(__('Currency'), 'currency_code')->options(function () {
                return array_filter(\App\Models\Currency::pluck('name', 'code')->all());
            })
                ->hideFromIndex()
                ->help(__('This currency will be used for all below prices')),

            Boolean::make(__('Setup fee'), 'setup_fee')
                ->sortable(),

            \Laravel\Nova\Fields\Currency::make(__('Setup fee amount'), 'setup_fee_amount')
                ->min(0)
                ->step(0.01)
                ->hideFromIndex()
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                )
                ->displayUsing(function ($value) {
                    return sprintf('%s %s', $this->currency_code, number_format($value, 2, '.', ','));
                }),

            Number::make(__('Minimum per stl'), 'minimum_per_stl')
                ->min(0)
                ->step(0.01)
                ->sortable(),

            \Laravel\Nova\Fields\Currency::make(__('Price minimum per stl'), 'price_minimum_per_stl')
                ->min(0)
                ->step(0.01)
                ->hideFromIndex()
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                )
                ->displayUsing(function ($value) {
                    return sprintf('%s %s', $this->currency_code, number_format($value, 2, '.', ','));
                }),

            \Laravel\Nova\Fields\Currency::make(__('Price volume cc'), 'price_volume_cc')
                ->min(0)
                ->step(0.01)
                ->hideFromIndex()
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                )
                ->displayUsing(function ($value) {
                    return sprintf('%s %s', $this->currency_code, number_format($value, 2, '.', ','));
                }),

            \Laravel\Nova\Fields\Currency::make(__('Price surface cm2'), 'price_surface_cm2')
                ->min(0)
                ->step(0.01)
                ->hideFromIndex()
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                )
                ->displayUsing(function ($value) {
                    return sprintf('%s %s', $this->currency_code, number_format($value, 2, '.', ','));
                }),

            \Laravel\Nova\Fields\Currency::make(__('Fixed fee per part'), 'fixed_fee_per_part')
                ->min(0)
                ->step(0.01)
                ->hideFromIndex()
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                )
                ->displayUsing(function ($value) {
                    return sprintf('%s %s', $this->currency_code, number_format($value, 2, '.', ','));
                }),

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
