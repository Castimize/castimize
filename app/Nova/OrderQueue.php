<?php

namespace App\Nova;

use App\Nova\Actions\ExportLineItemsV1Action;
use App\Nova\Actions\PoAcceptedAtDcStatusAction;
use App\Nova\Actions\PoCanceledStatusAction;
use App\Nova\Actions\PoChangeStatusOrderManualAction;
use App\Nova\Actions\PoReprintByDcAction;
use App\Nova\Filters\DueDateDaterangepickerFilter;
use App\Nova\Filters\OrderDateDaterangepickerFilter;
use App\Nova\Filters\OrderQueueCountryFilter;
use App\Nova\Filters\OrderQueueMaterialFilter;
use App\Nova\Filters\OrderQueueOrderStatusFilter;
use App\Traits\Nova\CommonMetaDataTrait;
use App\Traits\Nova\OrderQueueStatusFieldTrait;
use Carbon\Carbon;
use Castimize\InlineTextEdit\InlineTextEdit;
use Exception;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Panel;
use Rpj\Daterangepicker\DateHelper;
use Titasgailius\SearchRelations\SearchesRelations;

class OrderQueue extends Resource
{
    use CommonMetaDataTrait;
    use OrderQueueStatusFieldTrait;
    use SearchesRelations;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\OrderQueue>
     */
    public static $model = \App\Models\OrderQueue::class;

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('PO\'s');
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function title()
    {
        return $this->customer_shipment_select_name;
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
        'created_at' => 'desc',
    ];

    /**
     * @var string[]
     */
    public static $with = [
        'manufacturer',
        'upload',
        'order.country',
        'shippingFee',
        'manufacturerShipment',
        'customerShipment',
        'orderQueueStatuses.orderStatus',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'order' => [
            'order_number',
            'billing_first_name',
            'billing_last_name',
            'shipping_first_name',
            'shipping_last_name',
            'billing_country',
            'shipping_country',
        ],
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
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Order'), 'order')
                ->canSee(function ($request) {
                    return $request->user()->isBackendUser();
                })
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Material'), function ($model) {
                return $model->upload->material_name;
            }),

            Text::make(__('Order'), function ($model) {
                return $model->order->order_number;
            })
                ->canSee(function ($request) {
                    return $request->user()->isManufacturer();
                })
                ->sortable(),

            Text::make(__('Customer'), function ($model) {
                return $model->order
                    ? '<span><a class="link-default" href="/admin/resources/customers/'.$model->order->customer_id.'">'.$model->order->billing_name.'</a></span>'
                    : '';
            })
                ->canSee(function ($request) {
                    return $request->user()->isBackendUser();
                })
                ->asHtml()
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Customer'), function ($model) {
                return $model->order->customer_id;
            })
                ->canSee(function ($request) {
                    return $request->user()->isManufacturer();
                })
                ->sortable(),

            Text::make(__('Country'), function ($model) {
                return $model->order ? strtoupper($model->order->country->alpha2) : null;
            })
                ->hideOnExport()
                ->sortable(),

            BelongsTo::make(__('Manufacturer'), 'manufacturer')
                ->hideFromIndex(function (ResourceIndexRequest $request) {
                    return $request->viaRelationship();
                })
                ->sortable(),

            Boolean::make(__('Status manual changed'), 'status_manual_changed')
                ->onlyOnDetail(),

            $this->getStatusField(),

            $this->getStatusCheckField(),

            Text::make(__('Days till TD'), function ($model) {
                $lastStatus = $model->getLastStatus();
                $dateNow = now();
                if ($lastStatus && ! $lastStatus?->orderStatus->end_status) {
                    $targetDate = Carbon::parse($model->target_date);
                    if ($dateNow->gt($targetDate)) {
                        return '- '.round($targetDate->diffInDays($dateNow));
                    }

                    return round($dateNow->diffInDays($targetDate));
                }

                return '-';
            })
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Days till FAD'), function ($model) {
                $lastStatus = $model->getLastStatus();
                $dateNow = now();
                if ($lastStatus && ! $lastStatus?->orderStatus->end_status) {
                    $finalArrivalDate = Carbon::parse($model->final_arrival_date);
                    if ($dateNow->gt($finalArrivalDate)) {
                        return '- '.round($finalArrivalDate->diffInDays($dateNow));
                    }

                    return round($dateNow->diffInDays($finalArrivalDate));
                }

                return '-';
            })
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Quantity'), function ($model) {
                return $model->upload->quantity;
            })
                ->hideFromIndex()
                ->sortable(),

            Text::make(__('Order parts'), function ($model) {
                return $model->upload->model_parts;
            })
                ->hideFromIndex()
                ->sortable(),

            Text::make(__('Total parts'), function ($model) {
                return $model->upload->model_parts * $this->upload->quantity;
            })
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Total'), function ($model) {
                return $model->upload->total ? currencyFormatter((float) $model->upload->total, $model->upload->currency_code) : '';
            })
                ->hideOnExport()
                ->sortable(),

            DateTime::make(__('Order date'), 'created_at')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->hideOnExport()
                ->sortable(),

            DateTime::make(__('Due date'), 'due_date')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Arrived at'), function ($model) {
                return $model->order->arrived_at !== null ? Carbon::parse($model->order->arrived_at)->format('d-m-Y H:i:s') : '-';
            })
                ->hideOnExport()
                ->sortable(),

            HasOne::make(__('Reprint'), 'reprint')
                ->onlyOnDetail(),

            InlineTextEdit::make(__('Remarks'), 'remarks')
                ->help(__('Max 500 characters'))
                ->modelClass(\App\Models\OrderQueue::class),

            new Panel(__('History'), $this->commonMetaData(false, false, false, false)),
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
     *
     * @throws Exception
     */
    public function filters(NovaRequest $request)
    {
        return [
            (new OrderQueueCountryFilter),
            (new OrderQueueMaterialFilter),
            (new OrderDateDaterangepickerFilter(DateHelper::ALL))
                ->setMaxDate(Carbon::today()),
            (new DueDateDaterangepickerFilter(DateHelper::ALL)),
            (new OrderQueueOrderStatusFilter),
        ];
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
            PoAcceptedAtDcStatusAction::make()->withoutConfirmation(),
            PoReprintByDcAction::make()
                ->confirmText(__('Are you sure you want to reprint the selected PO\'s?'))
                ->confirmButtonText(__('Confirm'))
                ->cancelButtonText(__('Cancel')),
            PoCanceledStatusAction::make()
                ->confirmText(__('Are you sure you want to cancel and refund the selected PO\'s?'))
                ->confirmButtonText(__('Confirm'))
                ->cancelButtonText(__('Cancel')),
            PoChangeStatusOrderManualAction::make()
                ->confirmText(__('Are you sure you want to change the status manually from the selected PO\'s?'))
                ->confirmButtonText(__('Confirm'))
                ->cancelButtonText(__('Cancel')),
            ExportLineItemsV1Action::make()
                ->confirmText(__('Are you sure you want to export the selected PO\'s?'))
                ->confirmButtonText(__('Export'))
                ->cancelButtonText(__('Cancel')),
        ];
    }
}
