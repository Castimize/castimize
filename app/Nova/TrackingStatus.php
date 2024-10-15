<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Panel;

class TrackingStatus extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\TrackingStatus>
     */
    public static $model = \App\Models\TrackingStatus::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function title()
    {
        return sprintf('%s, %s', $this->modelShipment->id, $this->status);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'status',
        'sub_status',
        'status_date',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'created_at' => 'desc',
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

            Text::make(__('Object ID'), 'object_id')
                ->sortable(),

            DateTime::make(__('Status'), 'status_date')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->sortable(),

            Text::make(__('Status'), 'status')
                ->sortable(),

            Text::make(__('Sub status'), 'sub_status'),

            Text::make(__('Status details'), 'status_details'),

            MorphTo::make(__('Shipment'))->types([
                CustomerShipment::class,
                ManufacturerShipment::class,
            ])
                ->hideFromIndex(function (ResourceIndexRequest $request) {
                    return $request->viaRelationship();
                }),

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
