<?php

namespace App\Nova;

use App\Nova\Actions\AcceptRejectionAction;
use App\Nova\Actions\DeclineRejectionAction;
use App\Traits\Nova\CommonMetaDataTrait;
use DigitalCreative\ColumnToggler\ColumnTogglerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
    use ColumnTogglerTrait;
    use CommonMetaDataTrait;

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
     * @var string[]
     */
    public static $with = [
        'order',
        'manufacturer',
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
                ->path('admin/rejections')
                ->maxWidth(1024)
                ->thumbnail(function ($value, $disk) {
                    return 'data: image/png;base64,'.base64_encode(Storage::disk('r2_private')->get($value));
                })
                ->preview(function ($value, $disk) {
                    return 'data: image/png;base64,'.base64_encode(Storage::disk('r2_private')->get($value));
                }),

            DateTime::make(__('Approved at'), 'approved_at')
                ->exceptOnForms(),

            DateTime::make(__('Declined at'), 'declined_at')
                ->exceptOnForms(),
        ];
    }

    /**
     * Get the fields displayed by the resource on index page.
     *
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

            Text::make(__('PO number'), function ($model) {
                return '<span><a class="link-default" href="/admin/resources/order-queues/'.$model->order_queue_id.'">'.$model->order_queue_id.'</a></span>';
            })
                ->asHtml()
                ->sortable(),

            Text::make(__('Customer'), function ($model) {
                return $model->order
                    ? '<span><a class="link-default" href="/admin/resources/customers/'.$model->order->customer_id.'">'.$model->order->billing_name.'</a></span>'
                    : '';
            })
                ->asHtml()
                ->sortable(),

            BelongsTo::make(__('Manufacturer'), 'manufacturer')
                ->sortable(),

            StatusField::make(__('Status'))
                ->icons([
                    'x-circle' => $this->declined_at !== null,
                    'check-circle' => $this->approved_at !== null,
                ])
                ->tooltip([
                    'x-circle' => __('Declined at :date', [
                        'date' => $this->declined_at,
                    ]),
                    'check-circle' => __('Accepted at :date', [
                        'date' => $this->approved_at,
                    ]),
                ])
                ->color([
                    'x-circle' => 'red-500',
                    'check-circle' => 'green-500',
                ])
                ->canSee(function () {
                    return $this->resource->approved_at !== null || $this->resource->declined_at !== null;
                }),

            Text::make(__('Reason'), 'reason_manufacturer'),

            Text::make(__('Note manufacturer'), 'note_manufacturer'),
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
        return [
            AcceptRejectionAction::make()
                ->confirmText(__('Are you sure you want to accept the rejection from the selected PO\'s?'))
                ->confirmButtonText(__('Accept rejection'))
                ->cancelButtonText(__('Cancel')),
            DeclineRejectionAction::make()
                ->confirmText(__('Are you sure you want to decline the rejection from the selected PO\'s?'))
                ->confirmButtonText(__('Decline rejection'))
                ->cancelButtonText(__('Cancel')),
        ];
    }
}
