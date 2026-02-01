<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Country extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Country>
     */
    public static $model = \App\Models\Country::class;

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
        'alpha2',
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
     * @var string[]
     */
    public static $with = [
        'logisticsZone',
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

            Text::make(__('Name'), 'name')
                ->sortable()
                ->required()
                ->rules('max:255'),

            Text::make(__('Iso 2'), 'alpha2')
                ->sortable()
                ->required()
                ->rules('max:255'),

            Text::make(__('Iso 3'), 'alpha3')
                ->sortable()
                ->required()
                ->rules('max:255')
                ->hideFromIndex(),

            BelongsTo::make(__('Logistics zone'), 'logisticsZone')
                ->sortable(),

            new Panel(__('History'), $this->commonMetaData()),
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
        return [];
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
