<?php

namespace App\Nova;

use App\Nova\Actions\OrderManualRefundAction;
use App\Nova\Filters\CreatedAtDaterangepickerFilter;

use App\Nova\Filters\StatusFilter;
use App\Traits\Nova\CommonMetaDataTrait;
use Carbon\Carbon;
use DigitalCreative\ColumnToggler\ColumnTogglerTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Rpj\Daterangepicker\DateHelper;
use Tomodo531\FilterableFilters\FilterableFilters;

class Order extends Resource
{
    use CommonMetaDataTrait, ColumnTogglerTrait;

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
                    return $this->customer_country;
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

            Text::make(__('Total refund'), function () {
                    return $this->total_refund ? currencyFormatter((float)$this->total_refund, $this->currency_code) : '';
                })
                ->hideByDefault()
                ->sortable(),

            Text::make(__('Created at'), function () {
                    return Carbon::parse($this->created_at)->format('d-m-Y H:i:s');
                })
                ->sortable(),

            Text::make(__('Paid at'), function () {
                if ($this->paid_at === null) {
                    return __('Not paid');
                }
                return Carbon::parse($this->paid_at)->format('d-m-Y H:i:s');
            })
                ->sortable(),

            Text::make(__('Payment method'), 'payment_method')
                ->hideByDefault(),

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

            Text::make(__('Paid at'), function () {
                if ($this->paid_at === null) {
                    return __('Not paid');
                }
                    return Carbon::parse($this->paid_at)->format('d-m-Y H:i:s');
                })
                ->sortable(),

            Text::make(__('Payment method'), 'payment_method'),

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

            Text::make(__('Total refund'), function () {
                return $this->total_refund ? currencyFormatter((float)$this->total_refund ,$this->currency_code) : '';
            }),

            BelongsTo::make(__('Customer'), 'customer'),

            Text::make(__('Country'), function () {
                    return $this->customer_country;
                })
                ->sortable(),

            Text::make(__('Phone'), 'billing_phone_number'),

            Text::make(__('Email'), 'email'),

            Textarea::make(__('Customer note'), 'comments'),

            Panel::make('Billing address', $this->billingAddressFields()),

            Panel::make('Shipping address', $this->shippingAddressFields()),

            HasMany::make(__('Uploads'), 'uploads'),

            HasMany::make(__('Reprints'), 'reprints'),

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
        return [
            (new OrderManualRefundAction($this->model()))
                ->showInline()
                ->exceptOnIndex()
                ->confirmText(__('Are you sure you want to refund this order?'))
                ->confirmButtonText(__('Confirm'))
                ->cancelButtonText(__('Cancel')),
        ];
    }

    /**
     * @return mixed
     */
    protected function getStatusField(): mixed
    {
        return Text::make(__('Status'), function () {
            return match ($this->status) {
                'pending' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Pending') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(107 114 128)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>',
                'processing' => '<span data-toggle="tooltip" data-placement="top" title="' . __('In process since :date', ['date' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s')]) . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(6 182 212)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></span>',
                'overdue' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Overdue since :days', ['days' => $this->daysOverdue()]) . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(249 115 22)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>',
                'almost-overdue' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Almost overdue') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></span>',
                'completed' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Completed') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(34 197 94)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>',
                'canceled' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Canceled') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(239 68 68)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>',
                default => ''
            };
        })
            ->hideOnExport()
            ->asHtml();

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
                return $this->billing_company;
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
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing last name'), 'billing_last_name')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing company'), 'billing_company')
//                ->sizeOnForms('w-full')
                ->onlyOnForms(),

            Text::make(__('Billing address line 1'), 'billing_address_line1')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing address line 2'), 'billing_address_line2')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing postal code'), 'billing_postal_code')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing city'), 'billing_city')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Billing state'), 'billing_state'),

            Text::make(__('Billing country'), 'billing_country'),

            Text::make(__('Billing vat number'), 'billing_vat_number'),

            Text::make(__('Billing phone'), 'billing_phone_number')
                ->hideFromIndex(),

            Text::make(__('Billing email'), 'billing_email')
                ->hideFromIndex(),
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

            Text::make(__('Shipping company'), function () {
                return $this->shipping_company;
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
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping last name'), 'shipping_last_name')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping company'), 'shipping_company')
//                ->sizeOnForms('w-full')
                ->onlyOnForms(),

            Text::make(__('Shipping address line 1'), 'shipping_address_1')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping address line 2'), 'shipping_address_2')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping postal code'), 'shipping_postal_code')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping city'), 'shipping_city')
//                ->sizeOnForms('w-1/2')
                ->onlyOnForms(),

            Text::make(__('Shipping state'), 'shipping_state'),

            Text::make(__('Shipping country'), 'shipping_country'),

            Text::make(__('Shipping phone'), 'shipping_phone_number')
                ->hideFromIndex(),

            Text::make(__('Shipping email'), 'shipping_email')
                ->hideFromIndex(),
        ];
    }
}
