<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Address extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Address>
     */
    public static $model = \App\Models\Address::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function title()
    {
        return sprintf('%s %s, %s %s', $this->address_line1, $this->house_number, $this->postal_code, $this->city?->name);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'address_line1',
        'postal_code',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'address_line1' => 'asc',
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

            Text::make(__('Street / House number'), function () {
                return sprintf('%s %s', $this->address_line1, $this->house_number);
            })->onlyOnIndex(),

            Text::make(__('Street'), 'address_line1')
                ->hideFromIndex()
                ->required()
                ->sortable(),

            Text::make(__('House number'), 'house_number')
                ->hideFromIndex()
                ->required()
                ->sortable(),

            Text::make(__('Postal code'), 'postal_code')
                ->required()
                ->sortable(),

            BelongsTo::make(__('City'))
                ->showCreateRelationButton()
                ->sortable(),

            BelongsTo::make(__('State'))
                ->showCreateRelationButton()
                ->hideFromIndex()
                ->sortable(),

            BelongsTo::make(__('Country'))
                ->sortable(),
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
        return [];
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
