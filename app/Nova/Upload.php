<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use App\Traits\Nova\OrderQueueStatusFieldTrait;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Panel;

class Upload extends Resource
{
    use CommonMetaDataTrait, OrderQueueStatusFieldTrait;

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
        'name',
        'file_name',
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

            Text::make(__('Name'), 'name')
                ->sortable(),

            File::make(__('Stl file'), 'file_name')
                ->disk('r2')
                ->path('wp-content/uploads/p3d/')
                ->acceptedTypes('.stl,.obj,.3ds'),

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

            Text::make(__('Manufacturer'), function () {
                return $this->orderQueue?->manufacturer
                    ? '<span><a class="link-default" href="/admin/resources/maufacturers/' . $this->orderQueue->manufacturer->id . '">' . $this->orderQueue->manufacturer->name . '</a></span>'
                    : '';
                })
                ->asHtml()
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

            BelongsTo::make(__('Order'), 'order')
                ->hideFromIndex(function (ResourceIndexRequest $request) {
                    return $request->viaRelationship();
                })
                ->sortable(),

            Text::make(__('Name'), 'name')
                ->sortable(),

            $this->getStatusField(),

            BelongsTo::make(__('Material'), 'material')
                ->sortable(),

            Number::make(__('Quantity'), 'quantity'),

            Text::make(__('Price'), function () {
                return $this->price ? currencyFormatter((float)$this->price, $this->currency_code) : '';
            })
                ->sortable(),

            Text::make(__('Manufacturer'), function () {
                return $this->orderQueue?->manufacturer
                    ? '<span><a class="link-default" href="/admin/resources/maufacturers/' . $this->orderQueue->manufacturer->id . '">' . $this->orderQueue->manufacturer->name . '</a></span>'
                    : '';
            })
                ->asHtml()
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
}
