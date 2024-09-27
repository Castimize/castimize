<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class OrderQueue extends Model
{
    use HasFactory, RevisionableTrait, Userstamps, SoftDeletes;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_queue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'manufacturer_id',
        'upload_id',
        'order_id',
        'shipping_fee_id',
        'manufacturer_shipment_id',
        'manufacturer_cost_id',
        'customer_shipment_id',
        'contract_date',
        'manufacturer_costs',
        'total',
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
            'contract_date' => 'datetime',
            'status' => 'string',
            'status_slug' => 'string',
            'on_schedule' => 'boolean',
        ];
    }

    /**
     * Interact with manufacturer_costs
     */
    protected function manufacturerCosts(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  status
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->statuses?->last()->status,
        );
    }

    /**
     * Interact with  status_slug
     */
    protected function statusSlug(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->statuses?->last()->slug,
        );
    }

    /**
     * Interact with  on_schedule
     */
    protected function onSchedule(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->isOnSchedule(),
        );
    }

    /**
     * @return BelongsTo
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * @return BelongsTo
     */
    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo
     */
    public function shippingFee(): BelongsTo
    {
        return $this->belongsTo(ShippingFee::class);
    }

    /**
     * @return BelongsTo
     */
    public function manufacturerShipment(): BelongsTo
    {
        return $this->belongsTo(ManufacturerShipment::class);
    }

    /**
     * @return BelongsTo
     */
    public function manufacturerCost(): BelongsTo
    {
        return $this->belongsTo(ManufacturerCost::class);
    }

    /**
     * @return BelongsTo
     */
    public function customerShipment(): BelongsTo
    {
        return $this->belongsTo(CustomerShipment::class);
    }

    /**
     * @return HasMany
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(OrderQueueStatus::class);
    }

    /**
     * @return HasOne
     */
    public function rejection(): HasOne
    {
        return $this->hasOne(Rejection::class);
    }

    /**
     * @return bool
     */
    public function isOnSchedule(): bool
    {
        $statusSlug = $this->statusSlug;
        $finalArrivalDate = CarbonImmutable::parse($this->created_at)->addDays($this->upload->customer_lead_time);

        $targetDate = match ($statusSlug) {
            'in-queue' => Carbon::parse($this->created_at)->businessDays(1, 'add'),
            'rejection-request' => Carbon::parse($this->rejection->created_at)->businessDays(1, 'add'),
            'in-production' => $this->contract_date,
            'available-for-shipping' => $this->getAvailableForShippingDate($finalArrivalDate),
        };

        //Final arrival date = Date ordered + customer_lead_time
        //In queue
        //Target date: date orderded + 1 business day
        //Rejection request
        //Target date: rejections.created_at + 1 business day
        //in production
        //Target date: contract-date
        //available for shipping
        //Dichstbijzijnde datum van:
        //OF: Target date: Final arrival date - shipping_fees.default_lead_time - 1 business day - manufacturing_costs.shipment_lead_time
        //OF: available for shipping + 2 business days
        //in_transit_to_dc
        //Dichtstbijzijnde datum van:
        //OF: Target date: Final arrival date - shipping_fees.default_lead_time - 1 business day
        //OF: manufacturing.shipments.sent_at + manufacturing_costs.shipment_lead_time
        //at_dc
        //Target date: Final arrival date - shipping_fees.default_lead_time
        //In transit to customer
        //Target date: Final arrival date

        return true;
    }

    private function getAvailableForShippingDate(CarbonImmutable $finalArrivalDate)
    {
        $lastStatus = $this->statuses?->last();
        // shipping_fees.default_lead_time - 1 business day - manufacturing_costs.shipment_lead_time
        $subDays = $this->order->country->logisticsZone->shippingFee->default_lead_time;
        $targetDate = $finalArrivalDate;
        if ($lastStatus->slug !== 'available-for-shipping') {

        }
        $availableForShippingStatusDateCheck = $lastStatus->created_at;
        //order.country.logisticsZone.shippingFee
        //Dichstbijzijnde datum van:
        //OF: Target date: Final arrival date - shipping_fees.default_lead_time - 1 business day - manufacturing_costs.shipment_lead_time
        //OF: available for shipping + 2 business days
    }
}
