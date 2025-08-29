<?php

namespace App\Nova\Filters;

use App\Traits\Nova\Metrics\CustomMetricsQueries;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class RangesFilter extends Filter
{
    use CustomMetricsQueries;

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Create a new filter instance.
     *
     * @return void
     */
    public function __construct(protected string $column) {}

    /**
     * Apply the filter to the given query.
     *
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        return $this->addRangeToQuery($this->column, $value, $query);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(NovaRequest $request)
    {
        return $this->defaultValueRanges();
    }
}
