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

    protected $with = ['upload'];

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
        'due_date',
        'final_arrival_date',
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
            'due_date' => 'datetime',
            'final_arrival_date' => 'datetime',
            'contract_date' => 'datetime',
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
     * @return mixed
     */
    public function getLastStatus(): mixed
    {
        return $this->orderQueueStatuses->last();
    }

    /**
     * Interact with  status
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getLastStatus()?->status,
        );
    }

    /**
     * Interact with  status_slug
     */
    protected function statusSlug(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getLastStatus()?->slug,
        );
    }

    /**
     * Interact with  on_schedule
     */
    protected function targetDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getLastStatus()->target_date,
        );
    }

    /**
     * Interact with  on_schedule
     */
    protected function onSchedule(): Attribute
    {
        return Attribute::make(
            get: fn () => !now()->gte($this->target_date),
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
    public function orderQueueStatuses(): HasMany
    {
        return $this->hasMany(OrderQueueStatus::class);
    }

    public function latestOrderQueueStatus(): HasOne
    {
        return $this->hasOne(OrderQueueStatus::class)->latest();
    }

    /**
     * @return HasOne
     */
    public function rejection(): HasOne
    {
        return $this->hasOne(Rejection::class);
    }


    /**
     * @param $statusSlug
     * @return mixed
     */
    public function calculateTargetDate($statusSlug): mixed
    {
        return match ($statusSlug) {
            'in-queue' => Carbon::parse($this->created_at)->addBusinessDays(1),
            'rejection-request' => Carbon::parse($this->rejection->created_at)->addBusinessDays(1),
            'in-production' => $this->contract_date,
            'available-for-shipping' => $this->getAvailableForShippingDate($this->final_arrival_date),
            'in-transit-to-dc' => $this->getInTransitToDcDate($this->final_arrival_date),
            'at-dc' => $this->final_arrival_date->subBusinessDays($this->shippingFee->default_lead_time),
            default => $this->final_arrival_date,
        };
    }

    /**
     * @param Carbon $finalArrivalDate
     * @return Carbon
     */
    private function getAvailableForShippingDate(Carbon $finalArrivalDate): Carbon
    {
        // Closest date of:
        // OR: Target date: Final arrival date - shipping_fees.default_lead_time - 1 business day - manufacturing_costs.shipment_lead_time
        // OR: available for shipping + 2 business days
        $lastStatus = $this->getLastStatus();
        $targetDate = $finalArrivalDate->subBusinessDays($this->shippingFee->default_lead_time - $this->manufacturerCost->shipment_lead_time - 1);
        if (!$lastStatus || $lastStatus->slug !== 'available-for-shipping') {
            return $targetDate;
        }
        $availableForShippingStatusDateCheck = Carbon::parse($lastStatus->created_at)->addBusinessDays(2);
        return $targetDate->lt($availableForShippingStatusDateCheck) ? $targetDate : $availableForShippingStatusDateCheck;
    }

    /**
     * @param Carbon $finalArrivalDate
     * @return Carbon
     */
    private function getInTransitToDcDate(Carbon $finalArrivalDate): Carbon
    {
        // Closest date of:
        // OR: Target date: Final arrival date - shipping_fees.default_lead_time - 1 business day
        // OR: manufacturing.shipments.sent_at + manufacturing_costs.shipment_lead_time
        $lastStatus = $this->getLastStatus();
        $targetDate = $finalArrivalDate->subBusinessDays($this->shippingFee->default_lead_time - 1);
        if (!$lastStatus || $lastStatus->slug !== 'in-transit-to-dc') {
            return $targetDate;
        }
        $inTransitToDcStatusDateCheck = Carbon::parse($this->manufacturerShipment->sent_at)->addBusinessDays($this->manufacturerCost->shipment_lead_time);
        return $targetDate->lt($inTransitToDcStatusDateCheck) ? $targetDate : $inTransitToDcStatusDateCheck;
    }
}
