<?php

namespace App\Nova\Manufacturer;

use App\Nova\Actions\PoAcceptedAtDcStatusAction;
use App\Nova\Actions\PoChangeStatusOrderManualAction;
use App\Nova\Actions\ExportLineItemsV1Action;
use App\Nova\Filters\DueDateDaterangepickerFilter;
use App\Nova\Filters\OrderDateDaterangepickerFilter;
use App\Nova\Filters\OrderQueueCountryFilter;
use App\Nova\Filters\OrderQueueOrderStatusFilter;
use App\Nova\Resource;
use App\Traits\Nova\CommonMetaDataTrait;
use App\Traits\Nova\OrderQueueStatusFieldTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Rpj\Daterangepicker\DateHelper;
use Titasgailius\SearchRelations\SearchesRelations;

class PO extends Resource
{
    use CommonMetaDataTrait, OrderQueueStatusFieldTrait, SearchesRelations;

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
        return sprintf('%s-%s', $this->order->order_number, $this->id);
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
        'order',
        'shippingFee',
        'manufacturerShipment',
        'customerShipment',
        'orderQueueStatuses',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'order' => [
            'order_number',
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
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Order'), 'order')
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Customer'), function () {
                    return $this->order
                        ? '<span><a class="link-default" href="/admin/resources/customers/' . $this->order->customer_id . '">' . $this->order->billing_name . '</a></span>'
                        : '';
                })
                ->asHtml()
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Country'), function () {
                    return strtoupper($this->order->country->alpha2);
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

            Text::make(__('Days till TD'), function () {
                    $lastStatus = $this->getLastStatus();
                    $dateNow = now();
                    if ($lastStatus && !$lastStatus?->orderStatus->end_status) {
                        $targetDate = Carbon::parse($this->target_date);
                        if ($dateNow->gt($targetDate)) {
                            return '- ' . round($targetDate->diffInDays($dateNow));
                        }
                        return round($dateNow->diffInDays($targetDate));
                    }

                    return '-';
                })
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Days till FAD'), function () {
                    $lastStatus = $this->getLastStatus();
                    $dateNow = now();
                    if ($lastStatus && !$lastStatus?->orderStatus->end_status) {
                        $finalArrivalDate = Carbon::parse($this->final_arrival_date);
                        if ($dateNow->gt($finalArrivalDate)) {
                            return '- ' . round($finalArrivalDate->diffInDays($dateNow));
                        }
                        return round($dateNow->diffInDays($finalArrivalDate));
                    }

                    return '-';
                })
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Order parts'), function () {
                    return $this->order->order_parts;
                })
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Total'), function () {
                    return $this->order->total ? currencyFormatter((float)$this->order->total, $this->order->currency_code) : '';
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

            Text::make(__('Arrived at'), function () {
                    return $this->order->arrived_at !== null ? Carbon::parse($this->order->arrived_at)->format('d-m-Y H:i:s') : '-';
                })
                ->hideOnExport()
                ->sortable(),
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
     * @throws Exception
     */
    public function filters(NovaRequest $request)
    {
        return [
//            (new OrderQueueCountryFilter()),
            (new OrderDateDaterangepickerFilter( DateHelper::ALL))
                ->setMaxDate(Carbon::today()),
            (new DueDateDaterangepickerFilter( DateHelper::ALL)),
            (new OrderQueueOrderStatusFilter()),
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
        return [
            ExportLineItemsV1Action::make(),
        ];
    }
}
