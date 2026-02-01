<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource as NovaResource;

abstract class Resource extends NovaResource
{
    public function __construct($resource = null)
    {
        if (auth()->user()->isBackendUser()) {
            Nova::withBreadcrumbs();
        }
        parent::__construct($resource);
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        // Eager load editor relationship to prevent N+1 queries from CommonMetaDataTrait
        // Only if the model has the editor relationship (from Userstamps trait)
        $model = $query->getModel();
        if (method_exists($model, 'editor')) {
            $query->with(['editor']);
        }

        if ($request->has('orderBy') && empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy(key(static::$sort), reset(static::$sort));
        }

        return $query;
    }

    /**
     * Build a Scout search query for the given resource.
     *
     * @param  \Laravel\Scout\Builder  $query
     * @return \Laravel\Scout\Builder
     */
    public static function scoutQuery(NovaRequest $request, $query)
    {
        return $query;
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        return parent::detailQuery($request, $query);
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return parent::relatableQuery($request, $query);
    }
}
