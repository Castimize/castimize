<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use Castimize\ColumnToggler\ColumnTogglerTrait;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Panel;

class Model extends Resource
{
//    use ColumnTogglerTrait;
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
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Model name'), 'model_name')
                ->onlyOnDetail(),

            Text::make(__('Name'), 'name')
                ->sortable(),

            File::make(__('Stl file'), 'file_name')
                ->disk('s3')
                ->path('wp-content/uploads/p3d/')
                ->acceptedTypes('.stl,.obj,.3ds'),

            BelongsTo::make(__('Customer'), 'customer')
                ->hideFromIndex(function (ResourceIndexRequest $request) {
                    return $request->viaRelationship();
                })
                ->sortable(),

            BelongsToMany::make(__('Materials'), 'materials')
                ->sortable(),

            Text::make(__('Model volume cc'), 'model_volume_cc')
                ->hideByDefault(),

            Text::make(__('Model x length'), 'model_x_length')
                ->hideByDefault(),

            Text::make(__('Model y length'), 'model_y_length')
                ->hideByDefault(),

            Text::make(__('Model z length'), 'model_z_length')
                ->hideByDefault(),

            Text::make(__('Model surface area cm2'), 'model_surface_area_cm2')
                ->hideByDefault(),

            Text::make(__('Model box volume'), 'model_box_volume')
                ->hideByDefault(),

            Number::make(__('Model parts'), 'model_parts')
                ->hideFromIndex(function (ResourceIndexRequest $request) {
                    return $request->viaRelationship();
                })
                ->step(1),

            Code::make(__('Categories'), 'categories')
                ->json(),

            new Panel(__('History'), $this->commonMetaData(showCreatedAtOnIndex: true, showUpdatedAtOnIndex: false, showEditorOnIndex: false)),
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
