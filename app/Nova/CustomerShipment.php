<?php

namespace App\Nova;

use App\Enums\Shippo\ShippoDistanceUnitsEnum;
use App\Enums\Shippo\ShippoMassUnitsEnum;
use App\Nova\Actions\ShipmentInTransitToCustomerStatusAction;
use App\Nova\Filters\CreatedAtDaterangepickerFilter;
use App\Nova\Settings\Shipping\DcSettings;
use App\Nova\Settings\Shipping\ParcelSettings;
use App\Traits\Nova\CommonMetaDataTrait;
use Carbon\Carbon;
use Castimize\SelectWithOverview\SelectWithOverview;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Rpj\Daterangepicker\DateHelper;

/**
 * @property string|null $tracking_number
 * @property string|null $tracking_url
 */
class CustomerShipment extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\CustomerShipment>
     */
    public static $model = \App\Models\CustomerShipment::class;

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Customer shipments');
    }

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
        'tracking_number',
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
        'customer',
        'currency',
        'orderQueues.order',
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

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToReplicate(Request $request)
    {
        return false;
    }

    /**
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->withCount('orderQueues as order_queues_count');
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy(key(static::$sort), reset(static::$sort));
        }

        return $query;
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        $query->withCount('orderQueues as order_queues_count');

        return parent::detailQuery($request, $query);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Order ID\'s'), function ($model) {
                $links = [];
                foreach ($model->orderQueues as $orderQueue) {
                    if (! array_key_exists($orderQueue->order_id, $links)) {
                        $links[$orderQueue->order_id] = '<a class="link-default" href="/admin/resources/orders/'.$orderQueue->order_id.'" target="_blank">'.$orderQueue->order->order_number.'</a>';
                    }
                }

                return implode(', ', $links);
            })
                ->asHtml()
                ->exceptOnForms()
                ->sortable(),

            Text::make(__('Tracking number'), function () {
                if (empty($this->tracking_url)) {
                    return $this->tracking_number;
                }

                return sprintf('<a class="link-default" href="%s" target="_blank">%s</a>', $this->tracking_url, $this->tracking_number);
            })
                ->asHtml()
                ->exceptOnForms()
                ->sortable(),

            Text::make(__('Label url'), function () {
                if (empty($this->label_url)) {
                    return '';
                }

                return '<a class="link-default" href="'.$this->label_url.'" target="_blank">'.__('Label').'</a>';
            })
                ->asHtml()
                ->exceptOnForms()
                ->sortable(),

            Text::make(__('Commercial invoice url'), function () {
                if (empty($this->commercial_invoice_url)) {
                    return '';
                }

                return '<a class="link-default" href="'.$this->commercial_invoice_url.'" target="_blank">'.__('Commercial invoice').'</a>';
            })
                ->asHtml()
                ->onlyOnDetail()
                ->sortable(),

            Text::make(__('QR code url'), function () {
                if (empty($this->qr_code_url)) {
                    return '';
                }

                return '<a class="link-default" href="'.$this->qr_code_url.'" target="_blank">'.__('QR code').'</a>';
            })
                ->asHtml()
                ->onlyOnDetail()
                ->sortable(),

            Number::make('# Of PO\'s', 'order_queues_count')
                ->exceptOnForms()
                ->sortable(),

            HasMany::make(__('PO\'s'), 'orderQueues', OrderQueue::class)
                ->onlyOnDetail(),

            DateTime::make(__('Sent at'), 'sent_at')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->exceptOnForms()
                ->sortable(),

            DateTime::make(__('Expected delivery date'), 'expected_delivery_date')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->exceptOnForms()
                ->sortable(),

            DateTime::make(__('Arrived at'), 'arrived_at')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->exceptOnForms()
                ->sortable(),

            Number::make('Total parts', 'total_parts')
                ->exceptOnForms()
                ->sortable(),

            Text::make(__('Total costs'), function () {
                return $this->total_costs ? currencyFormatter((float) $this->total_costs, $this->currency_code) : '';
            })
                ->exceptOnForms()
                ->sortable(),

            Code::make(__('Shipment meta data'), 'shippo_shipment_meta_data')
                ->canSee(function (NovaRequest $request) {
                    return $request->user()->isSuperAdmin();
                }),

            Code::make(__('Shipment meta data'), 'shippo_transaction_meta_data')
                ->canSee(function (NovaRequest $request) {
                    return $request->user()->isSuperAdmin();
                })
                ->json(),

            MorphMany::make(__('Tracking history'), 'trackingStatuses', TrackingStatus::class),

            new Panel(__('History'), $this->commonMetaData(showUpdatedAtOnIndex: false, showEditorOnIndex: false)),
        ];
    }

    /**
     * Get the fields displayed by the resource on create page.
     *
     * @return array
     */
    public function fieldsForCreate(NovaRequest $request)
    {
        $dcSettings = (new DcSettings);
        $parcelSettings = (new ParcelSettings);

        return [
            SelectWithOverview::make('PO\'s', 'selectedPOs')
                ->placeholder(__('Select PO\'s'))
                ->options(\App\Models\OrderQueue::getAtDcOrderQueueOptions())
                ->overviewHeaders(\App\Models\OrderQueue::getOverviewHeaders()),

            Heading::make('<h3 class="font-normal text-xl">'.__('From address').'</h3>')->asHtml(),

            Text::make(__('Name'), 'from_address_name')
                ->default($dcSettings->name),

            Text::make(__('Company'), 'from_address_company')
                ->default($dcSettings->company),

            Text::make(__('Address 1'), 'from_address_address_line1')
                ->default($dcSettings->addressLine1),

            Text::make(__('Address 2'), 'from_address_address_line2')
                ->default($dcSettings->addressLine2),

            Text::make(__('Postal code'), 'from_address_postal_code')
                ->default($dcSettings->postalCode),

            Text::make(__('City'), 'from_address_city')
                ->default($dcSettings->city),

            Text::make(__('State'), 'from_address_state')
                ->default($dcSettings->state),

            Text::make(__('Country'), 'from_address_country')
                ->default($dcSettings->country),

            Text::make(__('Phone'), 'from_address_phone')
                ->default($dcSettings->phone),

            Text::make(__('Email'), 'from_address_email')
                ->default($dcSettings->email),

            Heading::make('<h3 class="font-normal text-xl">'.__('To address').'</h3>')->asHtml(),

            Text::make(__('Name'), 'to_address_name')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['name'];
                        }
                    }
                ),

            Text::make(__('Company'), 'to_address_company')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['company'];
                        }
                    }
                ),

            Text::make(__('Address 1'), 'to_address_address_line1')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['address_line1'];
                        }
                    }
                ),

            Text::make(__('Address 2'), 'to_address_address_line2')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['address_line2'];
                        }
                    }
                ),

            Text::make(__('Postal code'), 'to_address_postal_code')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['postal_code'];
                        }
                    }
                ),

            Text::make(__('City'), 'to_address_city')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['city'];
                        }
                    }
                ),

            Text::make(__('State'), 'to_address_state')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['state'];
                        }
                    }
                ),

            Text::make(__('Country'), 'to_address_country')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['country'];
                        }
                    }
                ),

            Text::make(__('Phone'), 'to_address_phone')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['phone'];
                        }
                    }
                ),

            Text::make(__('Email'), 'to_address_email')
                ->dependsOn(
                    ['selectedPOs'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if (is_array($formData->selectedPOs) && count($formData->selectedPOs) > 0) {
                            $firstPO = $formData->selectedPOs[0];
                            $toAddress = Cache::remember('selectedPOs-'.$firstPO.'-toAddress', 60, function () use ($firstPO) {
                                $orderQueue = \App\Models\OrderQueue::with(['order'])->find($firstPO);
                                if ($orderQueue === null) {
                                    return [];
                                }

                                return $orderQueue->order->shipping_address;
                            });
                            $field->value = $toAddress['email'];
                        }
                    }
                ),

            Heading::make('<h3 class="font-normal text-xl">'.__('Parcel settings').'</h3>')->asHtml(),

            Select::make(__('Distance unit'), 'parcel_distance_unit')
                ->default($parcelSettings->distanceUnit)
                ->options(ShippoDistanceUnitsEnum::values())
                ->displayUsingLabels(),

            Number::make(__('Length'), 'parcel_length')
                ->default($parcelSettings->length),

            Number::make(__('Width'), 'parcel_width')
                ->default($parcelSettings->width),

            Number::make(__('Height'), 'parcel_height')
                ->default($parcelSettings->height),

            Select::make(__('Mass unit'), 'parcel_mass_unit')
                ->default($parcelSettings->massUnit)
                ->options(ShippoMassUnitsEnum::values())->displayUsingLabels(),

            Number::make(__('Weight'), 'parcel_weight')
                ->default($parcelSettings->weight),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     *
     * @throws Exception
     */
    public function filters(NovaRequest $request)
    {
        return [
            (new CreatedAtDaterangepickerFilter(DateHelper::ALL))
                ->setMaxDate(Carbon::today()),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            ShipmentInTransitToCustomerStatusAction::make(),
        ];
    }
}
