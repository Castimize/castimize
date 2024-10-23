<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasFactory, RevisionableTrait, Userstamps, SoftDeletes;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'country_id',
        'customer_shipment_id',
        'currency_id',
        'wp_id',
        'order_number',
        'order_key',
        'first_name',
        'last_name',
        'email',
        'billing_first_name',
        'billing_last_name',
        'billing_phone_number',
        'billing_email',
        'billing_address_line1',
        'billing_address_line2',
        'billing_postal_code',
        'billing_city',
        'billing_state',
        'billing_country',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_phone_number',
        'shipping_email',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_postal_code',
        'shipping_city',
        'shipping_state',
        'shipping_country',
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
        'currency_code',
        'order_parts',
        'payment_method',
        'payment_issuer',
        'payment_intent_id',
        'customer_ip_address',
        'customer_user_agent',
        'meta_data',
        'comments',
        'promo_code',
        'fast_delivery_lead_time',
        'is_paid',
        'paid_at',
        'order_customer_lead_time',
        'due_date',
        'arrived_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'fast_delivery_lead_time' => 'datetime',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
            'due_date' => 'datetime',
            'arrived_at' => 'datetime',
            'meta_data' => AsArrayObject::class,
            'billing_name' => 'string',
            'shipping_name' => 'string',
            'billing_address' => AsArrayObject::class,
            'shipping_address' => AsArrayObject::class,
        ];
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d-m-Y H:i:s');
    }

    /**
     * Interact with  service_fee
     */
    protected function serviceFee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  service_fee_tax
     */
    protected function serviceFeeTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  shipping_fee
     */
    protected function shippingFee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  shipping_fee_tax
     */
    protected function shippingFeeTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  discount_fee
     */
    protected function discountFee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  discount_fee_tax
     */
    protected function discountFeeTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  total
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  total_tax
     */
    protected function totalTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  production_cost
     */
    protected function productionCost(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  production_cost_tax
     */
    protected function productionCostTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with billing_name
     */
    protected function billingName(): Attribute
    {
        return Attribute::make(
            get: fn () => sprintf('%s %s', $this->billing_first_name, $this->billing_last_name),
        );
    }

    /**
     * @return Attribute
     */
    protected function billingAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'first_name' => $this->billing_first_name,
                'last_name' => $this->billing_first_name,
                'name' => $this->billing_name,
                'company' => $this->billing_company,
                'address_line1' => $this->billing_address_line1,
                'address_line2' => $this->billing_address_line2,
                'postal_code' => $this->billing_postal_code,
                'city' => $this->billing_city,
                'state' => $this->billing_state,
                'country' => $this->billing_country,
                'phone' => $this->billing_phone_number,
                'email' => $this->billing_email,
                'vat_number' => $this->billing_vat_number,
            ],
        );
    }

    /**
     * Interact with shipping_name
     */
    protected function shippingName(): Attribute
    {
        return Attribute::make(
            get: fn () => sprintf('%s %s', $this->shipping_first_name, $this->shipping_last_name),
        );
    }

    /**
     * @return Attribute
     */
    protected function shippingAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'first_name' => $this->shipping_first_name,
                'last_name' => $this->shipping_first_name,
                'name' => $this->shipping_name,
                'company' => $this->shipping_company,
                'address_line1' => $this->shipping_address_line1,
                'address_line2' => $this->shipping_address_line2,
                'postal_code' => $this->shipping_postal_code,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'country' => $this->shipping_country,
                'phone' => !empty($this->shipping_phone_number) ? $this->shipping_phone_number : $this->billing_phone_number,
                'email' => !empty($this->shipping_email) ? $this->shipping_email : $this->billing_email,
            ],
        );
    }

    /**
     * @return Attribute
     */
    protected function customerCountry(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->customer?->country?->name ?? '',
        );
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo
     */
    public function customerShipment(): BelongsTo
    {
        return $this->belongsTo(CustomerShipment::class);
    }

    /**
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return HasMany
     */
    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    /**
     * @return HasMany
     */
    public function orderQueues(): HasMany
    {
        return $this->hasMany(OrderQueue::class);
    }

    /**
     * @return HasMany
     */
    public function rejections(): HasMany
    {
        return $this->hasMany(Rejection::class);
    }

    /**
     * Get the days overdue
     * @return float|null
     */
    public function daysOverdue(): float|null
    {
        if ($this->status === 'overdue') {
            return now()->diffInDays($this->due_date);
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function totalOrderParts()
    {
        return $this->uploads()->sum('model_parts');
    }

    public function allOrderQueuesEndStatus(): bool
    {
        $return = true;
        foreach ($this->uploads as $upload) {
            $orderQueueStatus = $upload->orderQueue->getLastStatus();
            if (!$orderQueueStatus->orderStatus->end_status) {
                $return = false;
            }
        }
        return $return;
    }
}
