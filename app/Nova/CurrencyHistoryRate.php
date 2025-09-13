<?php

namespace App\Nova;


use App\Nova\Actions\GetCurrencyHistoryRate;
use App\Traits\Nova\CommonMetaDataTrait;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class CurrencyHistoryRate extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\CurrencyHistoryRate>
     */
    public static $model = \App\Models\CurrencyHistoryRate::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        return sprintf('%s (%s-%s)', $this->historical_date, $this->base_currency, $this->convert_currency);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'historical_date',
        'base_currency',
        'convert_currency',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'historical_date' => 'desc',
    ];

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
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

            Date::make(__('Historical date'), 'historical_date')
                ->sortable()
                ->required(),

            Text::make(__('Base currency'), 'base_currency')
                ->sortable()
                ->required(),

            Text::make(__('Convert currency'), 'convert_currency')
                ->sortable()
                ->required(),

            Number::make(__('Rate'), 'rate')
                ->sortable()
                ->required(),

            new Panel(__('History'), $this->commonMetaData()),
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
        return [
            GetCurrencyHistoryRate::make()
                ->standalone(),
        ];
    }
}
