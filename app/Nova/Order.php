<?php

namespace App\Nova;

use App\Nova\Filters\CreatedAtDaterangepicker;
use App\Nova\Filters\ShowDeleted;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Rpj\Daterangepicker\DateHelper;
use Rpj\Daterangepicker\Daterangepicker;
use Saumini\Count\RelationshipCount;
use Tomodo531\FilterableFilters\FilterableFilters;
use Wame\TelInput\TelInput;

class Order extends Resource
{
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
    public static $title = 'id';

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
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Number::make(__('Wordpress ID'), 'wp_id')
                ->hideFromIndex(),

            BelongsTo::make(__('Customer'), 'customer_id')
                ->hideFromIndex(),

            BelongsTo::make(__('Country'), 'country_id')
                ->hideFromIndex(),

            BelongsTo::make(__('Customer shipment'), 'customer_shipment_id')
                ->hideFromIndex(),

            Select::make(__('Currency'), 'currency_code')->options(function () {
                return array_filter(\App\Models\Currency::pluck('name', 'code')->all());
            })
                ->hideFromIndex()
                ->help(__('This currency will be used for all below prices')),

            Text::make(__('Order number'), 'order_number')
                ->sortable(),

            RelationshipCount::make(__('# Uploads'), 'uploads')
                ->onlyOnIndex()
                ->sortable(),

            Text::make(__('Order product value'), 'order_product_value',)
                ->sortable(),

            \Laravel\Nova\Fields\Currency::make(__('Total'), 'total')
                ->min(0)
                ->step(0.01)
                ->locale(config('app.format_locale'))
                ->dependsOn(
                    ['currency_code'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        $field->currency($formData->currency_code);
                    }
                ),

            Text::make(__('Name'), function () {
                return sprintf('%s %s', $this->first_name, $this->last_name);
            })->exceptOnForms(),

            Text::make(__('First name'), 'first_name')
                ->onlyOnForms(),

            Text::make(__('Last name'), 'last_name')
                ->onlyOnForms(),

            Text::make(__('Email'), 'email')
                ->hideFromIndex(),

            Panel::make('Billing address', $this->billingAddressFields()),

            Panel::make('Shipping address', $this->shippingAddressFields()),

            'service_id',
            'service_fee',
            'service_fee_tax',
            'shipping_fee',
            'shipping_fee_tax',
            'discount_fee',
            'discount_fee_tax',
            'total',
            'total_tax',
            'production_cost',
            'production_cost_tax',
            'order_parts',
            'payment_method',
            'payment_issuer',
            'payment_intent_id',
            'customer_ip_address',
            'customer_user_agent',
            'comments',
            'promo_code',
            'fast_delivery_lead_time',
            'is_paid',
            'paid_at',
            'order_customer_lead_time',
            'arrived_at',
        ];
    }

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
            (new CreatedAtDaterangepicker( DateHelper::ALL))
                ->setMaxDate(Carbon::today()),
            new ShowDeleted(),
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
     * @return array
     */
    protected function billingAddressFields(): array
    {
        return [
            Text::make(__('Billing name'), function () {
                return sprintf('%s %s', $this->billing_first_name, $this->billing_last_name);
            })->exceptOnForms(),

            Text::make(__('Billing first name'), 'billing_first_name')
                ->onlyOnForms(),

            Text::make(__('Billing last name'), 'billing_last_name')
                ->onlyOnForms(),

            Text::make(__('Billing address line 1'), 'billing_address_line1'),

            Text::make(__('Billing address line 2'), 'billing_address_line2')
                ->hideFromIndex(),

            Text::make(__('Billing house number'), 'billing_house_number'),

            Text::make(__('Billing postal code'), 'billing_postal_code'),

            Text::make(__('Billing city'), 'billing_city'),

            Text::make(__('Billing country'), 'billing_country'),

            TelInput::make(__('Billing phone'), 'billing_phone_number')
                ->hideFromIndex(),
        ];
    }

    /**
     * @return array
     */
    protected function shippingAddressFields(): array
    {
        return [
            Text::make(__('Shipping name'), function () {
                return sprintf('%s %s', $this->shipping_first_name, $this->shipping_last_name);
            })->exceptOnForms(),

            Text::make(__('Shipping first name'), 'shipping_first_name')
                ->onlyOnForms(),

            Text::make(__('Shipping last name'), 'shipping_last_name')
                ->onlyOnForms(),

            Text::make(__('Shipping address line 1'), 'shipping_address_line1'),

            Text::make(__('Shipping address line 2'), 'shipping_address_line2')
                ->hideFromIndex(),

            Text::make(__('Shipping house number'), 'shipping_house_number'),

            Text::make(__('Shipping postal code'), 'shipping_postal_code'),

            Text::make(__('Shipping city'), 'shipping_city'),

            Text::make(__('Shipping country'), 'shipping_country'),

            TelInput::make(__('Shipping phone'), 'shipping_phone_number')
                ->hideFromIndex(),
        ];
    }
}
