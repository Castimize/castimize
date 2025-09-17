<?php

namespace App\Nova\Filters;

use App\Models\Country;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class OrderQueueCountryFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __('Country');
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->whereHas('order', function ($q) use ($value) {
                $q->where('country_id', $value);
            });
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(NovaRequest $request)
    {
        $countryIds = Order::select('country_id')->distinct()->pluck('country_id')->toArray();
        $countries = Country::whereIn('id', $countryIds)->get();
        $array = [];
        foreach ($countries as $country) {
            $array[$country->alpha2] = $country->id;
        }
        return $array;
    }
}
