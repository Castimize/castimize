<?php

namespace App\Nova;

use App\Nova\Filters\CreatedAtDaterangepickerFilter;

use App\Nova\Filters\StatusFilter;
use App\Traits\Nova\CommonMetaDataTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Rpj\Daterangepicker\DateHelper;
use Rpj\Daterangepicker\Daterangepicker;
use Saumini\Count\RelationshipCount;
use Tomodo531\FilterableFilters\FilterableFilters;
use Wame\TelInput\TelInput;
use WesselPerik\StatusField\StatusField;

class Order extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Order>
     */
    public static $model = \App\Models\Order::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'order_number';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'customer_id',
        'order_number',
        'last_name',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'order_number' => 'desc',
    ];

    /**
     * @var string[]
     */
    public static $with = [
        'customer',
        'country',
        'currency',
        'uploads',
    ];

    /**
     * @param NovaRequest $request
     * @param $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->withCount('uploads as uploads');
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy(key(static::$sort), reset(static::$sort));
        }

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
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

            Text::make(__('Order number'), 'order_number')
                ->sortable(),

            BelongsTo::make(__('Customer'), 'customer')
                ->sortable(),

            Text::make(__('Country'), function () {
                    return $this->getCustomerCountry();
                })
                ->sortable(),

            $this->getStatusField(),

            Text::make(__('Order parts'), function () {
                    return $this->totalOrderParts();
                })
                ->sortable(),

            Text::make(__('Total'), function () {
                    return $this->total ? currencyFormatter((float)$this->total, $this->currency_code) : '';
                })
                ->sortable(),

            Text::make(__('Created at'), function () {
                    return Carbon::parse($this->created_at)->format('d-m-Y H:i:s');
                })
                ->sortable(),

            Text::make(__('Due date'), function () {
                    return $this->due_date ? Carbon::parse($this->due_date)->format('d-m-Y H:i:s') : '';
                })
                ->sortable(),

            Text::make(__('Arrived at'), function () {
                    return $this->arrived_at ? Carbon::parse($this->arrived_at)->format('d-m-Y H:i:s') : '-';
                })
                ->sortable(),
        ];
    }

    /**
     * Get the fields displayed by the resource on detail page.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fieldsForDetail(NovaRequest $request)
    {
        return [
            ID::make(),

            Text::make(__('Order number'), 'order_number'),

            $this->getStatusField(),

            Text::make(__('Created at'), function () {
                    return Carbon::parse($this->created_at)->format('d-m-Y H:i:s');
                })
                ->sortable(),

            Text::make(__('Due date'), function () {
                    return $this->due_date ? Carbon::parse($this->due_date)->format('d-m-Y H:i:s') : '';
                })
                ->sortable(),

            Text::make(__('Arrived at'), function () {
                    return $this->arrived_at !== null ? Carbon::parse($this->arrived_at)->format('d-m-Y H:i:s') : '-';
                })
                ->sortable(),

            Text::make(__('Order parts'), function () {
                return $this->totalOrderParts();
            }),

            Text::make(__('Total'), function () {
                return $this->total ? currencyFormatter((float)$this->total, $this->currency_code) : '';
            }),

            BelongsTo::make(__('Customer'), 'customer'),

            Text::make(__('Country'), function () {
                    return $this->getCustomerCountry();
                })
                ->sortable(),

            Text::make(__('Phone'), 'billing_phone_number'),

            Text::make(__('Email'), 'email'),

            Textarea::make(__('Customer note'), 'comments'),

            Panel::make('Billing address', $this->billingAddressFields()),

            Panel::make('Shipping address', $this->shippingAddressFields()),

            HasMany::make(__('Uploads'), 'uploads'),

            HasMany::make(__('Rejections'), 'rejections'),

            new Panel(__('History'), $this->commonMetaData(false, false, false, false)),
        ];
    }

    /**
     * Get the fields displayed by the resource on detail page.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fieldsForCreate(NovaRequest $request)
    {

    }

    /**
     * Get the fields displayed by the resource on detail page.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fieldsForUpdate(NovaRequest $request)
    {
        return [
            Text::make(__('First name'), 'first_name'),

            Text::make(__('Last name'), 'last_name'),

            Text::make(__('Phone'), 'billing_phone_number'),

            Text::make(__('Email'), 'email'),

            Textarea::make(__('Customer note'), 'comments'),

            Panel::make('Billing address', $this->billingAddressFields()),

            Panel::make('Shipping address', $this->shippingAddressFields()),
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
            FilterableFilters::make(\App\Models\Order::class)
                ->fields([
                    'country' => [
                        'title' => 'name',
                        'primarykey' => 'id',
                        'foreignkey' => 'country_id',
                    ],
                ]),
            (new CreatedAtDaterangepickerFilter( DateHelper::ALL))
                ->setMaxDate(Carbon::today()),
            new StatusFilter(),

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

    /**
     * @return StatusField
     */
    protected function getStatusField(): StatusField
    {
        return StatusField::make(__('Status'))
            ->icons([
                'dots-circle-horizontal' => $this->status === 'processing',
                'clock' => $this->status === 'overdue',
                'exclamation' => $this->status === 'almost-overdue',
                'check-circle' => $this->status === 'completed',
                'x-circle' => $this->status === 'canceled',
            ])
            ->tooltip([
                'dots-circle-horizontal' => __('In process since :date', ['date' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s')]),
                'clock' => __('Overdue since :days', ['days' => $this->daysOverdue()]),
                'exclamation' => __('Almost overdue'),
                'check-circle' => __('Completed'),
                'x-circle' => __('Cancelled'),
            ])
            ->info([
                'dots-circle-horizontal' => __('In process'),
                'clock' => __('Overdue since :days', ['days' => $this->daysOverdue()]),
                'exclamation' => __('Almost overdue'),
                'check-circle' => __('Completed'),
                'x-circle' => __('Cancelled'),
            ])
            ->color([
                'dots-circle-horizontal' => 'grey-500',
                'clock' => 'orange-500',
                'exclamation' => 'yellow-500',
                'check-circle' => 'green-500',
                'x-circle' => 'red-500',
            ]);
    }

    /**
     * @return array
     */
    protected function billingAddressFields(): array
    {
        return [
            // Detail fields
            Text::make(__('Billing name'), function () {
                return $this->billing_name;
            })->exceptOnForms(),

            Text::make(__('Billing company'), function () {
                return $this->billing_name;
            })->exceptOnForms(),

            Text::make(__('Billing address line 1'), 'billing_address_line1')
                ->onlyOnDetail(),

            Text::make(__('Billing address line 2'), 'billing_address_line2')
                ->onlyOnDetail(),

            Text::make(__('Billing postal code, City'), function () {
                return sprintf('%s, %s', $this->billing_postal_code, $this->billing_city);
            })->onlyOnDetail(),

            // Form fields
            Text::make(__('Billing first name'), 'billing_first_name')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing last name'), 'billing_last_name')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing company'), 'billing_company')
                ->sizeOnForms('w-full')
                ->onlyOnForms(),

            Text::make(__('Billing address line 1'), 'billing_address_line1')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing address line 2'), 'billing_address_line2')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing postal code'), 'billing_postal_code')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing city'), 'billing_city')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing country'), 'billing_country'),

            Text::make(__('Billing vat number'), 'billing_vat_number'),

            Text::make(__('Billing phone'), 'billing_phone_number'),
        ];
    }

    /**
     * @return array
     */
    protected function shippingAddressFields(): array
    {
        return [
            // Detail fields
            Text::make(__('Shipping name'), function () {
                return $this->shipping_name;
            })->exceptOnForms(),

            Text::make(__('Shipping address line 1'), 'shipping_address_line1')
                ->onlyOnDetail(),

            Text::make(__('Shipping address line 2'), 'shipping_address_line2')
                ->onlyOnDetail(),

            Text::make(__('Shipping postal code, City'), function () {
                return sprintf('%s, %s', $this->shipping_postal_code, $this->shipping_city);
            })->onlyOnDetail(),

            // Form fields
            Text::make(__('Shipping first name'), 'shipping_first_name')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping last name'), 'shipping_last_name')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping company'), 'shipping_company')
                ->sizeOnForms('w-full')
                ->onlyOnForms(),

            Text::make(__('Shipping address line 1'), 'shipping_address_1')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping address line 2'), 'shipping_address_2')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping postal code'), 'shipping_postal_code')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping city'), 'shipping_city')
                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping country'), 'shipping_country'),

            Text::make(__('Shipping phone'), 'shipping_phone_number')
                ->hideFromIndex(),
        ];
    }
}
