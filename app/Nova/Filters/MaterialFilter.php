<?php

namespace App\Nova\Filters;

use App\Models\Material;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class MaterialFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->whereHas('upload', function ($query) use ($value) {
            return $query->where('material_name', $value);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(NovaRequest $request)
    {
        return Material::all()
            ->pluck('name', 'name')
            ->toArray();
    }
}
