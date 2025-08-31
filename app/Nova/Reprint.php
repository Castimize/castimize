<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Reprint extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Reprint>
     */
    public static $model = \App\Models\Reprint::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function title()
    {
        return sprintf('%s-%s', $this->order_queue_id, $this->manufacturer_id);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
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

    public function authorizedToReplicate(Request $request)
    {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('PO'), 'orderQueue', OrderQueue::class)
                ->sortable(),

            BelongsTo::make(__('Manufacturer'), 'manufacturer')
                ->sortable(),

            BelongsTo::make(__('Reprint culprit'), 'reprintCulprit')
                ->sortable(),

            BelongsTo::make(__('Reprint reason'), 'reprintReason')
                ->sortable(),

            Textarea::make(__('Reason'), 'reason')
                ->help(__('Add extra explanation besides the default reprint reason'))
                ->sortable(),
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
