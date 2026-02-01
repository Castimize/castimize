<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Invoice extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Invoice>
     */
    public static $model = \App\Models\Invoice::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'invoice_number';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'customer_id',
        'invoice_number',
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
        'customer',
        'exactSalesEntries',
    ];

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
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the fields displayed by the resource on index page.
     *
     * @return array
     */
    public function fieldsForIndex(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Customer'), 'customer')
                ->sortable(),

            Text::make(__('Invoice Number'), 'invoice_number')
                ->sortable(),

            DateTime::make(__('Invoice Date'), 'invoice_date')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : '')
                ->sortable(),

            Text::make(__('Type'), 'debit')
                ->displayUsing(fn ($value) => $value ? __('Invoice') : __('Credit note'))
                ->sortable(),

            Text::make(__('Description'), 'description')
                ->sortable(),

            Text::make(__('Total'), function () {
                return $this->total ? currencyFormatter((float) $this->total, $this->currency_code) : '';
            }),

            Text::make(__('Exact entries'), function () {
                return $this->exactSalesEntries ? $this->exactSalesEntries->count() : '';
            })
                ->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),
        ];
    }

    /**
     * Get the fields displayed by the resource on index page.
     *
     * @return array
     */
    public function fieldsForDetail(NovaRequest $request)
    {
        return [
            ID::make(),

            BelongsTo::make(__('Customer'), 'customer'),

            Text::make(__('Invoice Number'), 'invoice_number'),

            DateTime::make(__('Invoice Date'), 'invoice_date')
                ->displayUsing(fn ($value) => $value ? $value->format('d-m-Y H:i:s') : ''),

            Text::make(__('Type'), 'debit')
                ->displayUsing(fn ($value) => $value ? __('Invoice') : __('Credit note')),

            Text::make(__('Description'), 'description'),

            Text::make(__('Currency'), 'currency_code'),

            Text::make(__('Total'), function () {
                return $this->total ? currencyFormatter((float) $this->total, $this->currency_code) : '';
            }),

            Text::make(__('Total tax'), function () {
                return $this->total_tax ? currencyFormatter((float) $this->total_tax, $this->currency_code) : '';
            }),

            Text::make(__('Paid at'), function () {
                if ($this->paid_at === null) {
                    return __('Not paid');
                }

                return Carbon::parse($this->paid_at)->format('d-m-Y H:i:s');
            })
                ->sortable(),

            Text::make(__('Email'), 'email'),

            Panel::make('Address', $this->addressFields()),

            HasMany::make(__('Lines'), 'lines', InvoiceLine::class),

            HasMany::make(__('Exact sales entries'), 'exactSalesEntries', InvoiceExactSalesEntry::class),

            Code::make(__('Meta data'), 'meta_data')
                ->json()
                ->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),

            new Panel(__('History'), $this->commonMetaData(false, false, false, false)),
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
     */
    public function filters(NovaRequest $request)
    {
        return [];
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
        return [];
    }

    private function addressFields(): array
    {
        return [
            // Detail fields
            Text::make(__('Contact person'), function () {
                return $this->contact_person;
            }),

            Text::make(__('Company'), function () {
                return $this->company;
            }),

            Text::make(__('Address line 1'), 'address_line1'),

            Text::make(__('Address line 2'), 'address_line2'),

            Text::make(__('Postal code, City'), function () {
                return sprintf('%s, %s', $this->postal_code, $this->city);
            })->onlyOnDetail(),

            Text::make(__('Country'), 'country'),

            Text::make(__('Vat number'), 'vat_number'),
        ];
    }
}
