<?php

namespace App\Nova\Lenses;

use App\Enums\Admin\OrderStatusesEnum;
use App\Nova\Actions\DownloadModelsAction;
use App\Nova\Actions\DownloadPoLabelsAction;
use App\Nova\Actions\PoReprintByManufacturerAction;
use App\Nova\Filters\ContractDateDaterangepickerFilter;
use App\Nova\Filters\EntryDateDaterangepickerFilter;
use App\Nova\Filters\MaterialFilter;
use App\Nova\Filters\OrderQueueOrderStatusFilter;
use App\Traits\Nova\ManufacturerPOFieldsTrait;
use Castimize\PoStatusCard\PoStatusCard;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;
use Rpj\Daterangepicker\DateHelper;

class AvailableForShipping extends Lens
{
    use ManufacturerPOFieldsTrait;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'created_at',
        'contract_date',
        'order.order_number',
        'upload.material_name',
    ];

    /**
     * Indicates whether the resource should automatically poll for new resources.
     *
     * @var bool
     */
    public static $polling = true;

    /**
     * The interval at which Nova should poll for new resources.
     *
     * @var int
     */
    public static $pollingInterval = 60;

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param LensRequest $request
     * @param  Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query->whereHasLastOrderQueueStatus(OrderStatusesEnum::AvailableForShipping->value)
                ->where('manufacturer_id', auth()->user()->manufacturer?->id)
                ->whereHas('order', function (Builder $query) {
                    $query->removeTestEmailAddresses('email')
                        ->removeTestCustomerIds('customer_id');
                })
        ));
    }

    /**
     * Get the fields available to the lens.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $fields = $this->manufacturerPOFields();
//        $fields[] = BelongsTo::make()
        return $fields;
    }

    /**
     * Get the cards available on the lens.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [
            PoStatusCard::make()->statuses([
                OrderStatusesEnum::InQueue->value => __('In queue'),
                OrderStatusesEnum::InProduction->value => __('In production'),
                OrderStatusesEnum::AvailableForShipping->value => __('Available for shipping'),
                OrderStatusesEnum::InTransitToDc->value => __('In transit to dc'),
                OrderStatusesEnum::AtDc->value => __('Completed'),
            ])
                ->activeSlug(OrderStatusesEnum::AvailableForShipping->value)
                ->refreshIntervalSeconds(),
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            (new MaterialFilter()),
            (new EntryDateDaterangepickerFilter( DateHelper::ALL)),
            (new ContractDateDaterangepickerFilter( DateHelper::ALL)),
            (new OrderQueueOrderStatusFilter()),
        ];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            PoReprintByManufacturerAction::make()
                ->confirmText(__('Are you sure you want to reprint the selected PO\'s?'))
                ->confirmButtonText(__('Reprint'))
                ->cancelButtonText(__('Cancel')),
            DownloadPoLabelsAction::make()
                ->confirmText(__('Are you sure you want to download the labels from the selected PO\'s?'))
                ->confirmButtonText(__('Download'))
                ->cancelButtonText(__('Cancel')),
            DownloadModelsAction::make()
                ->confirmText(__('Are you sure you want to download the models from the selected PO\'s?'))
                ->confirmButtonText(__('Download'))
                ->cancelButtonText(__('Cancel')),
        ];
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'available-for-shipping';
    }
}
