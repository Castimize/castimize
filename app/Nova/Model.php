<?php

namespace App\Nova;

use App\Nova\Filters\ShowDeleted;
use App\Traits\Nova\CommonMetaDataTrait;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Model extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Model>
     */
    public static $model = \App\Models\Model::class;

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
        'file_name',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'name' => 'asc',
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

            Text::make(__('Name'), 'name')
                ->sortable(),

            File::make(__('Stl file'))
                ->disk(env('FILESYSTEM_DISK'))
                ->path('website/wp-content/uploads/p3d/')
                ->acceptedTypes('.stl,.obj,.3ds'),

            BelongsTo::make(__('Customer'), 'customer')
                ->sortable(),

            BelongsTo::make(__('Material'), 'material')
                ->sortable(),

            Number::make(__('Model volume cc'), 'model_volume_cc')
                ->hideFromIndex()
                ->step(0.01),

            Number::make(__('Model x length'), 'model_x_length')
                ->hideFromIndex()
                ->step(0.01),

            Number::make(__('Model y length'), 'model_y_length')
                ->hideFromIndex()
                ->step(0.01),

            Number::make(__('Model z length'), 'model_z_length')
                ->hideFromIndex()
                ->step(0.01),

            Number::make(__('Model surface area cm2'), 'model_surface_area_cm2')
                ->hideFromIndex()
                ->step(0.01),

            Number::make(__('Model parts'), 'model_parts')
                ->step(1),

            Number::make(__('Model box volume'), 'model_box_volume')
                ->hideFromIndex()
                ->step(0.01),

            new Panel(__('History'), $this->commonMetaData(true, true, false, false)),
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
            new ShowDeleted(),
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
        return [];
    }
}
