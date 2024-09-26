<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use WesselPerik\StatusField\StatusField;

class Upload extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Upload>
     */
    public static $model = \App\Models\Upload::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

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

            BelongsTo::make(__('Material'), 'material')
                ->sortable(),

            Number::make(__('Quantity'), 'quantity'),

            \Laravel\Nova\Fields\Currency::make(__('Price'), 'total')
                ->locale(config('app.format_locale'))
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($this->currency_code);
                    }
                ),

            BelongsTo::make(__('Manufacturer'), 'manufacturer')
                ->sortable(),
//                        status
//                        In queue 🚦
//                        Rejection request ❎
//                        Cancelled ❌
//                        In production 🛠️
//                        Available for shipping ⚓️
//                                      In transit to DC 🚢
//                        At DC 🏭
//                        In transit to customer 📦
//                        Completed ✔
//                        Reprinted 🔙
//                        Duedate
//                        Date received
//                        T&T link (als line-item in shipment zit, is dit handig?)

            new Panel(__('History'), $this->commonMetaData(false, false, false, false)),
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

            Text::make(__('Name'), 'name')
                ->sortable(),

            $this->getStatusField(),

            BelongsTo::make(__('Material'), 'material')
                ->sortable(),

            Number::make(__('Quantity'), 'quantity'),

            Text::make(__('Price'), function () {
                return $this->price ? currencyFormatter((float)$this->price, $this->currency_code) : '';
            })
                ->exceptOnForms()
                ->sortable(),

            BelongsTo::make(__('Manufacturer'), 'manufacturer')
                ->sortable(),

            $this->getStatusField(),

            Text::make(__('Due date'), function () {
                return $this->due_date->format('d-m-Y H:i:s');
            }),

            Text::make(__('Completed at'), function () {
                $completedAt = $this->completed_at;
                return $completedAt ? $completedAt->format('d-m-Y H:i:s') : '-';
            }),

            Text::make(__('T&T link'), function () {
                if ($this->customerShipment) {
                    return $this->customerShipment->ups_tracking;
                }
                if ($this->manufacturerShipment) {
                    return $this->manufacturerShipment->ups_tracking;
                }

                return '-';
            }),
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

    /**
     * @return StatusField
     */
    protected function getStatusField(): StatusField
    {
//                        In queue 🚦
//                        Rejection request ❎
//                        Cancelled ❌
//                        In production 🛠️
//                        Available for shipping ⚓️
//                        In transit to DC 🚢
//                        At DC 🏭
//                        In transit to customer 📦
//                        Completed ✔
//                        Reprinted 🔙

        return StatusField::make(__('Status'))
            ->icons([
                'dots-circle-horizontal' => $this->status === 'in-queue',
                'x-circle' => $this->status === 'rejection-request',
                'x' => $this->status === 'cancelled',
                'cog' => $this->status === 'in-production',
                'clipboard-check' => $this->status === 'available-for-shipping',
                'truck' => $this->status === 'in-transit-to-dc',
                'office-building' => $this->status === 'at-dc',
                'badge-check' => $this->status === 'in-transit-to-customer',
                'check' => $this->status === 'completed',
                'printer' => $this->status === 'reprinted',
            ])
            ->tooltip([
                'dots-circle-horizontal' => __('In queue'),
                'x-circle' => __('Rejection request'),
                'x' => __('Cancelled'),
                'cog' => __('In production'),
                'clipboard-check' => __('Available for shipping'),
                'truck' => __('In transit to DC'),
                'office-building' => __('At DC'),
                'badge-check' => __('In transit to customer'),
                'check' => __('Completed'),
                'printer' => __('Reprinted'),
            ])
            ->color([
                'dots-circle-horizontal' => 'grey-500',
                'x-circle' => 'orange-500',
                'x' => 'redd-500',
                'cog' => 'yellow-500',
                'clipboard-check' => 'yellow-500',
                'truck' => 'yellow-500',
                'office-building' => 'yellow-500',
                'badge-check' => 'yellow-500',
                'check' => 'green-500',
                'printer' => 'purple-500',
            ]);
    }
}
