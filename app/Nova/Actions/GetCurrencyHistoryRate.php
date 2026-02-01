<?php

namespace App\Nova\Actions;

use App\Models\CurrencyHistoryRate;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class GetCurrencyHistoryRate extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Get a curerncy history rate for a date');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $historicalDate = Carbon::parse($fields->historical_date);
        $currencyHistoryRate = CurrencyHistoryRate::where('historical_date', $historicalDate->format('Y-m-d'))->first();
        if ($currencyHistoryRate) {
            return ActionResponse::danger(__('Currency history rate already exists.'));
        }

        Artisan::call('castimize:get-currency-historical-rates --historical-date='.$historicalDate->format('Y-m-d'));

        return ActionResponse::message(__('Currency history rate is being added.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Date::make(__('Historical date'), 'historical_date'),
        ];
    }
}
