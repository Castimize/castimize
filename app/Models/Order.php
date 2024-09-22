<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

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
        'first_name',
        'last_name',
        'email',
        'billing_first_name',
        'billing_last_name',
        'billing_phone_number',
        'billing_address_line1',
        'billing_address_line2',
        'billing_house_number',
        'billing_postal_code',
        'billing_city',
        'billing_country',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_phone_number',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_house_number',
        'shipping_postal_code',
        'shipping_city',
        'shipping_country',
        'order_product_value',
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
            'arrived_at' => 'datetime',
        ];
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
}
