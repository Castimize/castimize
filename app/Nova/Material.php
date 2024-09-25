<?php

namespace App\Nova;

use App\Nova\Filters\ShowDeleted;
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
     * @param NovaRequest $request
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

            Number::make(__('Customer lead time'), 'customer_lead_time')
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
                ->sortable(),

            Text::make(__('HS code'), 'hs_code')
                ->sortable(),

            Textarea::make(__('HS code description'), 'hs_code_description')
                ->hideByDefault(),

            Number::make(__('Minimum x length'), 'minimum_x_length')
                ->hideByDefault(),

            Number::make(__('Maximum x length'), 'maximum_x_length')
                ->hideByDefault(),

            Number::make(__('Minimum y length'), 'minimum_y_length')
                ->hideByDefault(),

            Number::make(__('Maximum y length'), 'maximum_y_length')
                ->hideByDefault(),

            Number::make(__('Minimum z length'), 'minimum_z_length')
                ->hideByDefault(),

            Number::make(__('Maximum z length'), 'maximum_z_length')
                ->hideByDefault(),

            Number::make(__('Minimum volume'), 'minimum_volume')
                ->hideByDefault(),

            Number::make(__('Maximum volume'), 'maximum_volume')
                ->hideByDefault(),

            Number::make(__('Minimum box volume'), 'minimum_box_volume')
                ->hideByDefault(),

            Number::make(__('Maximum box volume'), 'maximum_box_volume')
                ->hideByDefault(),

            Number::make(__('Discount'), 'discount')
                ->sizeOnDetail('w-1/4')
                ->hideByDefault()
                ->step(0.01)
                ->displayUsing(function ($value) {
                    return $value . '%';
                }),

            Number::make(__('Bulk discount 10'), 'bulk_discount_10')
                ->sizeOnDetail('w-1/4')
                ->hideByDefault()
                ->step(0.01)
                ->displayUsing(function ($value) {
                    return $value . '%';
                }),

            Number::make(__('Bulk discount 25'), 'bulk_discount_25')
                ->sizeOnDetail('w-1/4')
                ->hideByDefault()
                ->step(0.01)
                ->displayUsing(function ($value) {
                    return $value . '%';
                }),

            Number::make(__('Bulk discount 50'), 'bulk_discount_50')
                ->sizeOnDetail('w-1/4')
                ->hideByDefault()
                ->step(0.01)
                ->displayUsing(function ($value) {
                    return $value . '%';
                }),

            HasMany::make(__('Prices'), 'prices'),

            new Panel(__('History'), $this->commonMetaData(true, true, false, false)),
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
