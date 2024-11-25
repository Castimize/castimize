<?php

namespace App\Nova;

use Alexwenzel\DependencyContainer\DependencyContainer;
use App\Nova\Filters\ManufacturerFilter;
use App\Nova\Settings\Shipping\DcSettings;
use App\Nova\Settings\Shipping\ParcelSettings;
use App\Services\Shippo\ShippoService;
use Castimize\SelectManufacturerWithOverview\SelectManufacturerWithOverview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
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
use Laravel\Nova\Nova;

class ManufacturerShipment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ManufacturerShipment>
     */
    public static $model = \App\Models\ManufacturerShipment::class;

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Manufacturer shipments');
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
        'manufacturer',
        'currency',
        'orderQueues',
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
     * @param NovaRequest $request
     * @param $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->withCount('orderQueues as order_queues_count');
        if (empty($request->get('orderBy'))) {
            return $query->orderBy(key(static::$sort), reset(static::$sort));
        }

        return $query;
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param NovaRequest $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        $query->withCount('orderQueues as order_queues_count');
        return parent::detailQuery($request, $query);
    }

    /**
     * Return the location to redirect the user after creation.
     *
     * @param NovaRequest $request
     * @param Resource $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        if ($request->viaRelationship()) {
            return '/resources/' . app($request->viaResource())::uriKey() . '/' . $request->viaResourceId;
        }
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
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

            BelongsTo::make(__('Manufacturer'), 'manufacturer', Manufacturer::class)
                ->sortable(),

            Text::make(__('Order ID\'s'), function ($model) {
                $links = [];
                foreach ($model->orderQueues as $orderQueue) {
                    if (!array_key_exists($orderQueue->order->order_number, $links)) {
                        $links[$orderQueue->order->order_number] = '<a class="link-default" href="/admin/resources/orders/' . $orderQueue->order_id . '" target="_blank">' . $orderQueue->order->order_number . '</a>';
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
                return '<a class="link-default" href="' . $this->label_url . '" target="_blank">' . __('Label') . '</a>';
            })
                ->asHtml()
                ->exceptOnForms()
                ->sortable(),

            Text::make(__('Commercial invoice url'), function () {
                if (empty($this->commercial_invoice_url)) {
                    return '';
                }
                return '<a class="link-default" href="' . $this->commercial_invoice_url . '" target="_blank">' . __('Commercial invoice') . '</a>';
            })
                ->asHtml()
                ->onlyOnDetail()
                ->sortable(),

            Text::make(__('QR code url'), function () {
                if (empty($this->qr_code_url)) {
                    return '';
                }
                return '<a class="link-default" href="' . $this->qr_code_url . '" target="_blank">' . __('QR code') . '</a>';
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

            MorphMany::make(__('Tracking history'), 'trackingStatuses', TrackingStatus::class),
        ];
    }

    /**
     * Get the fields displayed by the resource on create page.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fieldsForCreate(NovaRequest $request)
    {
        $dcSettings = (new DcSettings());
        $parcelSettings = (new ParcelSettings());

        return [
            BelongsTo::make(__('Manufacturer'), 'manufacturer', Manufacturer::class)
                ->sortable(),

            SelectManufacturerWithOverview::make('PO\'s', 'selectedPOs')
                ->rules('required')
                ->required()
                ->placeholder(__('Select PO\'s'))
                ->options(\App\Models\OrderQueue::getAvailableForShippingOrderQueueOptions())
                ->overviewHeaders(\App\Models\OrderQueue::getOverviewHeaders()),

            Heading::make('<h3 class="font-normal text-xl">' . __('General') . '</h3>')
                ->asHtml()
                ->dependsOn(
                    ['manufacturer'],
                    function (Heading $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            if ($manufacturer && $manufacturer->can_handle_own_shipping) {
                                $field->show();
                            } else {
                                $field->hide();
                            }
                        }
                    }
                ),

            Boolean::make(__('Handle your own shipping'), 'handles_own_shipping')
                ->default(false)
                ->dependsOn(
                    ['manufacturer'],
                    function (Boolean $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            if ($manufacturer && $manufacturer->can_handle_own_shipping) {
                                $field->readonly(false);
                            } else {
                                $field->readonly(true);
                            }
                        }
                    }
                ),

            DependencyContainer::make([
                Heading::make('<h3 class="font-normal text-xl">' . __('Tracking') . '</h3>')
                    ->asHtml(),

                Text::make(__('Tracking number'), 'tracking_number'),

                Text::make(__('Tracking url'), 'tracking_url'),
            ])->dependsOn('handles_own_shipping', true),

                Heading::make('<h3 class="font-normal text-xl">' . __('From address') . '</h3>')
                    ->asHtml(),

                Text::make(__('Name'), 'from_address_name')
                    ->dependsOn(
                        ['manufacturer'],
                        function (Text $field, NovaRequest $request, FormData $formData) {
                            if ($formData->manufacturer) {
                                $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                    return \App\Models\Manufacturer::find($formData->manufacturer);
                                });
                                $field->setValue($manufacturer->contact_name_1);
                            }
                        }
                    ),

                Text::make(__('Company'), 'from_address_company')
                    ->dependsOn(
                        ['manufacturer'],
                        function (Text $field, NovaRequest $request, FormData $formData) {
                            if ($formData->manufacturer) {
                                $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                    return \App\Models\Manufacturer::find($formData->manufacturer);
                                });
                                $field->setValue($manufacturer->name);
                            }
                        }
                    ),

            Text::make(__('Address 1'), 'from_address_address_line1')
                ->dependsOn(
                    ['manufacturer'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            $field->setValue($manufacturer->address_line1);
                        }
                    }
                ),

            Text::make(__('Address 2'), 'from_address_address_line2')
                ->dependsOn(
                    ['manufacturer'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            $field->setValue($manufacturer->address_line2);
                        }
                    }
                ),

            Text::make(__('Postal code'), 'from_address_postal_code')
                ->dependsOn(
                    ['manufacturer'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            $field->setValue($manufacturer->postal_code);
                        }
                    }
                ),

            Text::make(__('City'), 'from_address_city')
                ->dependsOn(
                    ['manufacturer'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            $field->setValue($manufacturer->city?->name);
                        }
                    }
                ),

            Text::make(__('State'), 'from_address_state')
                ->dependsOn(
                    ['manufacturer'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            $field->setValue($manufacturer->state?->name);
                        }
                    }
                ),

            Text::make(__('Country'), 'from_address_country')
                ->dependsOn(
                    ['manufacturer'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            $field->setValue($manufacturer->country?->alpha2);
                        }
                    }
                ),

            Text::make(__('Phone'), 'from_address_phone')
                ->dependsOn(
                    ['manufacturer'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            $field->setValue($manufacturer->phone_1);
                        }
                    }
                ),

            Text::make(__('Email'), 'from_address_email')
                ->dependsOn(
                    ['manufacturer'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        if ($formData->manufacturer) {
                            $manufacturer = Cache::remember('manufacturer-shipment-' . $formData->manufacturer . '-create', 10, function () use ($formData, $field) {
                                return \App\Models\Manufacturer::find($formData->manufacturer);
                            });
                            $field->setValue($manufacturer->email);
                        }
                    }
                ),

            Heading::make('<h3 class="font-normal text-xl">' . __('To address') . '</h3>')
                ->asHtml(),

            Text::make(__('Name'), 'to_address_name')
                ->readonly()
                ->default($dcSettings->name),

            Text::make(__('Company'), 'to_address_company')
                ->readonly()
                ->default($dcSettings->company),

            Text::make(__('Address 1'), 'to_address_address_line1')
                ->readonly()
                ->default($dcSettings->addressLine1),

            Text::make(__('Address 2'), 'to_address_address_line2')
                ->readonly()
                ->default($dcSettings->addressLine2),

            Text::make(__('Postal code'), 'to_address_postal_code')
                ->readonly()
                ->default($dcSettings->postalCode),

            Text::make(__('City'), 'to_address_city')
                ->readonly()
                ->default($dcSettings->city),

            Text::make(__('State'), 'to_address_state')
                ->readonly()
                ->default($dcSettings->state),

            Text::make(__('Country'), 'to_address_country')
                ->readonly()
                ->default($dcSettings->country),

            Text::make(__('Phone'), 'to_address_phone')
                ->readonly()
                ->default($dcSettings->phone),

            Text::make(__('Email'), 'to_address_email')
                ->readonly()
                ->default($dcSettings->email),

            Heading::make('<h3 class="font-normal text-xl">' . __('Parcel settings') . '</h3>')
                ->asHtml(),

            Select::make(__('Distance unit'), 'parcel_distance_unit')
                ->default($parcelSettings->distanceUnit)
                ->options(ShippoService::DISTANCE_UNITS)
                ->displayUsingLabels(),

            Number::make(__('Length'), 'parcel_length')
                ->default($parcelSettings->length),

            Number::make(__('Width'), 'parcel_width')
                ->default($parcelSettings->width),

            Number::make(__('Height'), 'parcel_height')
                ->default($parcelSettings->height),

            Select::make(__('Mass unit'), 'parcel_mass_unit')
                ->default($parcelSettings->massUnit)
                ->options(ShippoService::MASS_UNITS)->displayUsingLabels(),

            Number::make(__('Weight'), 'parcel_weight')
                ->default($parcelSettings->weight),
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
        return [
            ManufacturerFilter::make(),
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
