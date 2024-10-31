<?php

namespace App\Nova\Manufacturer;

use App\Nova\Actions\DownloadModelsAction;
use App\Nova\Filters\ContractDateDaterangepickerFilter;
use App\Nova\Filters\EntryDateDaterangepickerFilter;
use App\Nova\Filters\MaterialFilter;
use App\Nova\Filters\OrderQueueOrderStatusFilter;
use App\Nova\Lenses\AtDc;
use App\Nova\Lenses\AvailableForShipping;
use App\Nova\Lenses\InProduction;
use App\Nova\Lenses\InQueue;
use App\Nova\Lenses\InTransitToDc;
use App\Nova\Resource;
use App\Traits\Nova\CommonMetaDataTrait;
use App\Traits\Nova\ManufacturerPOFieldsTrait;
use App\Traits\Nova\OrderQueueStatusFieldTrait;
use Castimize\PoStatusCard\PoStatusCard;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use SLASH2NL\NovaBackButton\NovaBackButton;

class Po extends Resource
{
    use CommonMetaDataTrait, OrderQueueStatusFieldTrait, ManufacturerPOFieldsTrait;

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

    public function __construct($resource = null)
    {
        Nova::withBreadcrumbs(false);
        parent::__construct($resource);
    }

    /**
     * @param NovaRequest $request
     * @param $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->where('manufacturer_id', auth()->user()->manufacturer->id);
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy(key(static::$sort), reset(static::$sort));
        }

        return $query;
    }

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
        return $this->manufacturerPOFields();
//            $this->getStatusField(),
//
//            $this->getStatusCheckField(),
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [
            NovaBackButton::make(__('Back'))
                ->onlyOnDetail(),
            PoStatusCard::make()->statuses([
                'in-queue' => __('In queue'),
                'in-production' => __('In production'),
                'available-for-shipping' => __('Available for shipping'),
                'in-transit-to-dc' => __('In transit to dc'),
                'at-dc' => __('Completed'),
            ])->refreshIntervalSeconds(),
        ];
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
            (new MaterialFilter()),
            EntryDateDaterangepickerFilter::make(),
            ContractDateDaterangepickerFilter::make(),
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
        return [
            InQueue::make(),
            InProduction::make(),
            AvailableForShipping::make(),
            InTransitToDc::make(),
            AtDc::make(),
        ];
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
            DownloadModelsAction::make()
                ->confirmText(__('Are you sure you want to download the models from the selected PO\'s?'))
                ->confirmButtonText(__('Download'))
                ->cancelButtonText(__('Cancel')),
        ];
    }
}
