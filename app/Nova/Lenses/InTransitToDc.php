<?php

namespace App\Nova\Lenses;

use App\Nova\Actions\DownloadModelsAction;
use App\Nova\Actions\PoReprintByManufacturerAction;
use App\Nova\Filters\DueDateDaterangepickerFilter;
use App\Nova\Filters\OrderDateDaterangepickerFilter;
use App\Nova\Filters\OrderQueueOrderStatusFilter;
use App\Traits\Nova\ManufacturerPOFieldsTrait;
use Carbon\Carbon;
use Castimize\PoStatusCard\PoStatusCard;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;
use Rpj\Daterangepicker\DateHelper;

class InTransitToDc extends Lens
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
     * Get the query builder / paginator for the lens.
     *
     * @param LensRequest $request
     * @param  Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query->whereHasLastOrderQueueStatus('in-transit-to-dc')
                ->where('manufacturer_id', auth()->user()->manufacturer?->id)
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
        return $this->manufacturerPOFields();
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
                'in-queue' => __('In queue'),
                'in-production' => __('In production'),
                'available-for-shipping' => __('Available for shipping'),
                'at-dc' => __('Completed'),
            ]),
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
            (new OrderDateDaterangepickerFilter( DateHelper::ALL))
                ->setMaxDate(Carbon::today()),
            (new DueDateDaterangepickerFilter( DateHelper::ALL)),
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
        return 'in-transit-to-dc';
    }
}