<?php

namespace App\Traits\Nova;

use App\Nova\ManufacturerShipment;
use Carbon\Carbon;
use Castimize\InlineTextEdit\InlineTextEdit;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;

trait ManufacturerPOFieldsTrait
{
    public function manufacturerPOFields(): array
    {
        return [
            ID::make(__('PO'), 'id')->sortable(),

            Text::make(__('Customer ID'), function () {
                return $this->upload->customer_id;
            }),

            Text::make(__('Order ID'), function() {
                return $this->order->order_number;
            })
                ->hideOnExport()
                ->sortable(),

            Text::make(__('Material'), function () {
                return $this->upload->material_name;
            })
                ->hideOnExport()
                ->sortable(),

            Number::make(__('# Models'), function () {
                return $this->upload->quantity;
            })
                ->sortable(),

            Number::make(__('# Model parts'), function () {
                return $this->upload->model_parts;
            })
                ->sortable(),

            Number::make(__('# Total parts'), function () {
                return $this->upload->model_parts * $this->upload->quantity;
            })
                ->sortable(),

            Number::make(__('Model volume'), function () {
                return $this->upload->model_volume_cc;
            })
                ->sortable(),

            Number::make(__('Model surface area'), function () {
                return $this->upload->model_surface_area_cm2;
            })
                ->sortable(),

            Number::make(__('Total surface area'), function () {
                return $this->upload->model_surface_area_cm2 * $this->upload->quantity;
            })
                ->sortable(),

            Text::make(__('Model costs'), function () {
                if ($this->upload->quantity > 0) {
                    return $this->manufacturer_costs && $this->manufacturer_costs > 0.00 ? currencyFormatter((float) ($this->manufacturer_costs / $this->upload->quantity), $this->currency_code) : '';
                }
                return $this->manufacturer_costs && $this->manufacturer_costs > 0.00 ? currencyFormatter((float) $this->manufacturer_costs, $this->currency_code) : '';
            })
                ->sortable(),

            Text::make(__('Total costs'), function () {
                return $this->manufacturer_costs && $this->manufacturer_costs > 0.00 ? currencyFormatter((float) $this->manufacturer_costs, $this->currency_code) : '';
            })
                ->sortable(),

            Text::make(__('Discount'), function () {
                return $this->upload->manufacturer_discount ? $this->upload->manufacturer_discount . '%' : '';
            }),

            Text::make(__('Purity mark'), function () {
                return $this->upload->material->materialGroup->name === 'Gold & Silver' ? __('Yes') : __('No');
            }),

            DateTime::make(__('Entry date'), 'created_at')
                ->displayUsing(fn ($value) => $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value, config('app.timezone'))->setTimezone(auth()->user()->timezone)->format('c') : '')
                ->sortable(),

            DateTime::make(__('Contract date'), 'contract_date')
                ->displayUsing(fn ($value) => $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value, config('app.timezone'))->setTimezone(auth()->user()->timezone)->format('c') : '')
                ->sortable(),

            InlineTextEdit::make(__('Remarks'), 'remarks')
                ->help(__('Max 500 characters'))
                ->modelClass(\App\Models\OrderQueue::class),

            HasOne::make(__('Manufacturer shipment'), 'manufacturerShipment', ManufacturerShipment::class)
                ->hideFromIndex(function (ResourceIndexRequest $request) {
                    return $request->viaRelationship();
                }),
        ];
    }
}
