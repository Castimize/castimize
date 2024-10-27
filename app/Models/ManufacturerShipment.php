<?php

namespace App\Models;

use App\Observers\ManufacturerShipmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([ManufacturerShipmentObserver::class])]
class ManufacturerShipment extends Model
{
    use HasFactory, RevisionableTrait, Userstamps, SoftDeletes;

    public $selectedPOs;
    public $fromAddress = [];
    public $toAddress = [];
    public $parcel = [];

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'manufacturer_id',
        'currency_id',
        'eta',
        'sent_at',
        'arrived_at',
        'expected_delivery_date',
        'total_parts',
        'total_costs',
        'currency_code',
        'type',
        'tracking_number',
        'tracking_url',
        'tracking_manual',
        'shippo_shipment_id',
        'shippo_shipment_meta_data',
        'shippo_transaction_id',
        'shippo_transaction_meta_data',
        'label_url',
        'commercial_invoice_url',
        'qr_code_url',
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
            'sent_at' => 'datetime',
            'arrived_at' => 'datetime',
            'time_in_transit' => 'integer',
            'expected_delivery_date' => 'datetime',
            'shippo_shipment_meta_data' => AsArrayObject::class,
            'shippo_transaction_meta_data' => AsArrayObject::class,
        ];
    }

    /**
     * Time in transit
     * date difference created_at en arrived_at. indien geen arrived_at dan date difference created_at en today()
     */
    protected function timeInTransit(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->arrived_at !== null ? $this->arrived_at->diffInDays($this->created_at) : now()->diffInDays($this->created_at),
        );
    }

    /**
     * Interact with total_costs
     */
    protected function totalCosts(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
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
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return HasMany
     */
    public function orderQueues(): HasMany
    {
        return $this->hasMany(OrderQueue::class);
    }

    /**
     * @return MorphMany
     */
    public function trackingStatuses(): MorphMany
    {
        return $this->morphMany(
            TrackingStatus::class,
            'model',
        );
    }
}
