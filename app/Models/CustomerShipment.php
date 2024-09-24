<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class CustomerShipment extends Model
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
        'currency_id',
        'sent_at',
        'arrived_at',
        'expected_delivery_date',
        'ups_tracking',
        'ups_tracking_manual',
        'ups_service',
        'total_parts',
        'total_costs',
        'service_lead_time',
        'service_costs',
        'currency_code',
        'type',
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
            'service_lead_time' => 'datetime',
            'ups_service'=> 'boolean',
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
     * Interact with service_costs
     */
    protected function serviceCosts(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
