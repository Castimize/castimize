<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class InvoiceLine extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\InvoiceLine>
     */
    public static $model = \App\Models\InvoiceLine::class;

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
        'invoice_id',
        'upload_name',
        'material_name',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'id' => 'desc',
    ];

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToReplicate(Request $request)
    {
        return false;
    }

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

            Text::make(__('Name'), 'upload_name')
                ->displayUsing(fn ($value) => $value !== '-' ? $value : __('Refund'))
                ->sortable(),

            Text::make(__('Material name'), 'material_name')
                ->sortable(),

            Number::make(__('Quantity'), 'quantity'),

            Text::make(__('Currency'), 'currency_code'),

            Text::make(__('Total'), function () {
                return $this->total ? currencyFormatter((float)$this->total, $this->currency_code) : '';
            }),

            Text::make(__('Total tax'), function () {
                return $this->total_tax ? currencyFormatter((float)$this->total_tax, $this->currency_code) : '';
            }),
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
