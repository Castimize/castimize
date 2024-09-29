<?php

namespace App\Nova;

use App\Nova\Filters\DueDateDaterangepickerFilter;
use App\Nova\Filters\OrderDateDaterangepickerFilter;
use App\Nova\Filters\OrderQueueCountryFilter;
use App\Nova\Filters\OrderQueueOrderStatusFilter;
use App\Traits\Nova\CommonMetaDataTrait;
use App\Traits\Nova\OrderQueueStatusFieldTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Rpj\Daterangepicker\DateHelper;
use Titasgailius\SearchRelations\SearchesRelations;

class OrderQueue extends Resource
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
        return __('Line items');
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
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Order'), 'order')
                ->sortable(),

            Text::make(__('Customer'), function () {
                    return $this->order
                        ? '<span><a class="link-default" href="/admin/resources/customers/' . $this->order->customer_id . '">' . $this->order->billing_name . '</a></span>'
                        : '';
                })
                ->asHtml()
                ->sortable(),

            Text::make(__('Country'), function () {
                    return strtoupper($this->order->country->alpha2);
                })
                ->sortable(),

            $this->getStatusField(),

            $this->getStatusCheckField(),

            Text::make(__('Days till TD'), function () {
                    $lastStatus = $this->getLastStatus();
                    //$finalArrivalDate = CarbonImmutable::parse($this->created_at)->addBusinessDays($this->upload->customer_lead_time);
                    return $lastStatus && !$lastStatus?->orderStatus->end_status ? round(now()->diffInDays($this->target_date)) : '-';
                })
                ->sortable(),

            Text::make(__('Days till FAD'), function () {
                    $lastStatus = $this->getLastStatus();
                    return $lastStatus && !$lastStatus?->orderStatus->end_status ? round(now()->diffInDays($this->final_arrival_date)) : '-';
                })
                ->sortable(),

            Text::make(__('Order parts'), function () {
                    return $this->order->order_parts;
                })
                ->sortable(),

            Text::make(__('Total'), function () {
                    return $this->order->total ? currencyFormatter((float)$this->order->total, $this->order->currency_code) : '';
                })
                ->sortable(),

            DateTime::make(__('Order date'), 'created_at')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->sortable(),

            DateTime::make(__('Due date'), 'due_date')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->sortable(),

            Text::make(__('Arrived at'), function () {
                    return $this->order->arrived_at !== null ? Carbon::parse($this->order->arrived_at)->format('d-m-Y H:i:s') : '-';
                })->sortable(),
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
            (new OrderQueueCountryFilter()),
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
        return [];
    }
}
