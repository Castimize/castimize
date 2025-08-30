<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use DigitalCreative\ColumnToggler\ColumnTogglerTrait;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Material extends Resource
{
    use ColumnTogglerTrait, CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Material>
     */
    public static $model = \App\Models\Material::class;

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
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Number::make(__('Wordpress ID'), 'wp_id')
                ->hideFromIndex(),

            Text::make(__('Name'), 'name')
                ->sortable(),

            BelongsTo::make(__('Material group'), 'materialGroup', MaterialGroup::class),

            Number::make(__('Dc lead time'), 'dc_lead_time')
                ->min(0)
                ->step(1)
                ->sortable(),

            Number::make(__('Fast delivery lead time'), 'fast_delivery_lead_time')
                ->min(0)
                ->step(1)
                ->sortable(),

            Select::make(__('Currency'), 'currency_code')->options(function () {
                return array_filter(\App\Models\Currency::pluck('name', 'code')->all());
            })
                ->hideFromIndex()
                ->help(__('This currency will be used for below price')),

            \Laravel\Nova\Fields\Currency::make(__('Fast delivery fee'), 'fast_delivery_fee')
                ->min(0)
                ->step(0.01)
                ->locale(config('app.format_locale'))
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                )
                ->onlyOnForms()
                ->sortable(),

            Text::make(__('Fast delivery fee'), function () {
                return $this->fast_delivery_fee ? currencyFormatter((float) $this->fast_delivery_fee, $this->currency_code) : '';
            })
                ->exceptOnForms()
                ->sortable(),

            Text::make(__('HS code'), 'hs_code')
                ->sortable(),

            Textarea::make(__('HS code description'), 'hs_code_description')
                ->hideByDefault(),

            Text::make(__('Minimum x length'), 'minimum_x_length')
                ->help(__('Unit in cm'))
                ->hideByDefault(),

            Text::make(__('Maximum x length'), 'maximum_x_length')
                ->help(__('Unit in cm'))
                ->hideByDefault(),

            Text::make(__('Minimum y length'), 'minimum_y_length')
                ->help(__('Unit in cm'))
                ->hideByDefault(),

            Text::make(__('Maximum y length'), 'maximum_y_length')
                ->help(__('Unit in cm'))
                ->hideByDefault(),

            Text::make(__('Minimum z length'), 'minimum_z_length')
                ->help(__('Unit in cm'))
                ->hideByDefault(),

            Text::make(__('Maximum z length'), 'maximum_z_length')
                ->help(__('Unit in cm'))
                ->hideByDefault(),

            Text::make(__('Minimum volume'), 'minimum_volume')
                ->hideByDefault(),

            Text::make(__('Maximum volume'), 'maximum_volume')
                ->hideByDefault(),

            Text::make(__('Minimum box volume'), 'minimum_box_volume')
                ->hideByDefault(),

            Text::make(__('Maximum box volume'), 'maximum_box_volume')
                ->hideByDefault(),

            Text::make(__('Density'), 'density')
                ->help(__('Unit in g/cm3'))
                ->hideByDefault(),

            Number::make(__('Discount'), 'discount')
                ->help(__('In percentage'))
                ->step(0.01)
                ->onlyOnForms(),

            Number::make(__('Bulk discount 10'), 'bulk_discount_10')
                ->help(__('In percentage, for :amount pieces', [
                    'amount' => 10,
                ]))
                ->step(0.01)
                ->onlyOnForms(),

            Number::make(__('Bulk discount 25'), 'bulk_discount_25')
                ->help(__('In percentage, for :amount pieces', [
                    'amount' => 25,
                ]))
                ->step(0.01)
                ->onlyOnForms(),

            Number::make(__('Bulk discount 50'), 'bulk_discount_50')
                ->help(__('In percentage, for :amount pieces', [
                    'amount' => 50,
                ]))
                ->step(0.01)
                ->onlyOnForms(),

            Text::make(__('Discount'), function () {
                return $this->discount ? $this->discount.'%' : '';
            })
                ->hideByDefault()
                ->exceptOnForms(),

            Text::make(__('Bulk discount 10'), function () {
                return $this->bulk_discount_10 ? $this->bulk_discount_10.'%' : '';
            })
                ->hideByDefault()
                ->exceptOnForms(),

            Text::make(__('Bulk discount 25'), function () {
                return $this->bulk_discount_25 ? $this->bulk_discount_25.'%' : '';
            })
                ->hideByDefault()
                ->exceptOnForms(),

            Text::make(__('Bulk discount 50'), function () {
                return $this->bulk_discount_50 ? $this->bulk_discount_50.'%' : '';
            })
                ->hideByDefault()
                ->exceptOnForms(),

            HasMany::make(__('Prices'), 'prices'),

            new Panel(__('History'), $this->commonMetaData(true, true, false, false)),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [

        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
