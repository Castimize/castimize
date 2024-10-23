<?php

namespace App\Nova;

use App\Nova\Actions\AcceptRejectionAction;
use App\Nova\Actions\DeclineRejectionAction;
use App\Traits\Nova\CommonMetaDataTrait;
use DigitalCreative\ColumnToggler\ColumnTogglerTrait;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use WesselPerik\StatusField\StatusField;

class Rejection extends Resource
{
    use ColumnTogglerTrait, CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Rejection>
     */
    public static $model = \App\Models\Rejection::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function title()
    {
        return sprintf('%s: %s %s', __('Rejection'), $this->manufacturer_id, $this->order_queue_id);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'order_queue_id',
        'upload_id',
        'order_id',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'id' => 'desc',
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

            BelongsTo::make(__('Order'), 'order'),

            DateTime::make(__('Order date'), function () {
                return $this->order_date;
            })
                ->exceptOnForms(),

            BelongsTo::make(__('Manufacturer'), 'manufacturer'),

            BelongsTo::make(__('Rejection reason'), 'rejectionReason'),

            BelongsTo::make(__('Order queue'), 'orderQueue'),

            Textarea::make(__('Note manufacturer'), 'note_manufacturer'),

            Textarea::make(__('Note Castimize'), 'note_castimize'),

            Image::make(__('Photo'), 'photo')
                ->disk('r2_private')
                ->path('admin/rejections'),

            DateTime::make(__('Approved at'), 'approved_at')
                ->exceptOnForms(),

            DateTime::make(__('Declined at'), 'declined_at')
                ->exceptOnForms(),
        ];
    }

    /**
     * Get the fields displayed by the resource on index page.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fieldsForIndex(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Order'), 'order')
                ->sortable(),

            DateTime::make(__('Order date'), function () {
                return $this->order_date;
            })
                ->sortable(),

            BelongsTo::make(__('Upload'), 'upload')
                ->sortable(),

            BelongsTo::make(__('Manufacturer'), 'manufacturer')
                ->sortable(),

            StatusField::make(__('Status'))
                ->icons([
                    'x-circle' => $this->declined_at !== null,
                    'check-circle' => $this->accepted_at !== null,
                ])
                ->tooltip([
                    'x-circle' => __('Declined at :date', ['date' => $this->declined_at]),
                    'check-circle' => __('Accepted at :date', ['date' => $this->accepted_at]),
                ])
                ->color([
                    'x-circle' => 'red-500',
                    'check-circle' => 'green-500',
                ])
                ->canSee(function () {
                    return $this->resource->accepted_at !== null || $this->resource->declined_at !== null;
                }),

            Image::make(__('Photo'), 'photo'),

            Text::make(__('Reason'), 'reason_manufacturer'),

            Text::make(__('Note manufacturer'), 'note_manufacturer'),
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
        return [
            (new AcceptRejectionAction()),
            (new DeclineRejectionAction()),
        ];
    }
}
