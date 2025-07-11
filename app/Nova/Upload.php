<?php

namespace App\Nova;

use App\Nova\Actions\PoCanceledStatusAction;
use App\Nova\Actions\PoSetManufacturerDiscountAction;
use App\Nova\Actions\UploadToOrderQueueAction;
use App\Traits\Nova\CommonMetaDataTrait;
use App\Traits\Nova\OrderQueueStatusFieldTrait;
use Castimize\InlineTextEdit\InlineTextEdit;
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

            \Laravel\Nova\Fields\Currency::make(__('Refund total'), 'total_refund')
                ->locale(config('app.format_locale'))
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($this->currency_code);
                    }
                ),

            Text::make(__('Manufacturer discount'), function () {
                return $this->manufacturer_discount ? $this->manufacturer_discount . '%' : '';
            })
                ->hideByDefault()
                ->onlyOnDetail(),

            Number::make(__('Manufacturer discount'), 'manufacturer_discount')
                ->help(__('In percentage'))
                ->onlyOnForms()
                ->step(0.01),

            Text::make(__('Manufacturer'), function () {
                return $this->orderQueue?->manufacturer
                    ? '<span><a class="link-default" href="/admin/resources/maufacturers/' . $this->orderQueue->manufacturer->id . '">' . $this->orderQueue->manufacturer->name . '</a></span>'
                    : '';
                })
                ->asHtml()
                ->sortable(),

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
                return $this->total ? currencyFormatter((float)$this->total, $this->currency_code) : '';
            })
                ->sortable(),

            InlineTextEdit::make(__('Manufacturer discount'), 'manufacturer_discount')
                ->help(__('In percentage like 0,05 for 5%'))
                ->modelClass(\App\Models\Upload::class),

            Text::make(__('Manufacturer'), function () {
                return $this->orderQueue?->manufacturer
                    ? '<span><a class="link-default" href="/admin/resources/maufacturers/' . $this->orderQueue->manufacturer->id . '">' . $this->orderQueue->manufacturer->name . '</a></span>'
                    : '';
            })
                ->asHtml()
                ->sortable(),

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

            BelongsTo::make(__('Order queue'), 'orderQueue')
                ->canSee(function () {
                    return $this->orderQueue;
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
        return [
            PoCanceledStatusAction::make()
                ->confirmText(__('Are you sure you want to cancel and refund the selected uploads?'))
                ->confirmButtonText(__('Confirm'))
                ->cancelButtonText(__('Cancel')),
            PoSetManufacturerDiscountAction::make()
                ->confirmText(__('Are you sure you want to set manufacturer discount and recalculate manufacturer costs for the selected uploads?'))
                ->confirmButtonText(__('Confirm'))
                ->cancelButtonText(__('Cancel')),
            UploadToOrderQueueAction::make()
                ->confirmText(__('Are you sure you want to set these uploads to the order queue?'))
                ->confirmButtonText(__('Confirm'))
                ->cancelButtonText(__('Cancel')),
        ];
    }
}
