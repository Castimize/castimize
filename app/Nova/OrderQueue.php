<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use App\Traits\Nova\OrderQueueStatusFieldTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class OrderQueue extends Resource
{
    use CommonMetaDataTrait, OrderQueueStatusFieldTrait;

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
        return sprintf('%s %s', $this->order->order_number, $this->id);
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
        'statuses',
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
                    return $this->order->country->name;
                })
                ->sortable(),

            $this->getStatusField(),

            $this->getStatusCheckField(),

            Text::make(__('Country'), function () {
                    return $this->order->country->name;
                })
                ->sortable(),

            Text::make(__('Days till target date'), function () {
                    $lastStatus = $this->statuses?->last();
                    return $lastStatus && !$lastStatus->end_status ? now()->diffInDays($this->target_date) : '-';
                })
                ->sortable(),

            Text::make(__('Days till final arrival date'), function () {
                    $lastStatus = $this->statuses?->last();
                    return $lastStatus && !$lastStatus->end_status ? now()->diffInDays($this->final_arrival_date) : '-';
                })
                ->sortable(),

            Text::make(__('Order parts'), function () {
                    return $this->order->order_parts;
                })
                ->sortable(),

            Text::make(__('Total'), function () {
                    return $this->order->total ? currencyFormatter((float)$this->order->total, $this->currency_code) : '';
                })
                ->sortable(),

            Text::make(__('Order date'), function () {
                    return Carbon::parse($this->created_at)->format('d-m-Y H:i:s');
                })
                ->sortable(),

            Text::make(__('Due date'), function () {
                    return $this->order->due_date->format('d-m-Y H:i:s');
                })
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
