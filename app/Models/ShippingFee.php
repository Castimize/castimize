<?php

namespace App\Models;

use App\Observers\ShippingFeeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([ShippingFeeObserver::class])]
class ShippingFee extends Model
{
    use HasFactory, SoftDeletes, RevisionableTrait, Userstamps;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected  $fillable = [
        'logistics_zone_id',
        'currency_id',
        'name',
        'default_rate',
        'currency_code',
        'default_lead_time',
        'cc_threshold_1',
        'rate_increase_1',
        'cc_threshold_2',
        'rate_increase_2',
        'cc_threshold_3',
        'rate_increase_3',
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
        ];
    }

    /**
     * Interact with default_rate
     */
    protected function defaultRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with rate_increase_1
     */
    protected function rateIncrease1(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value * 100,
            set: fn ($value) => $value / 100,
        );
    }

    /**
     * Interact with rate_increase_2
     */
    protected function rateIncrease2(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value * 100,
            set: fn ($value) => $value / 100,
        );
    }

    /**
     * Interact with rate_increase_3
     */
//    protected function rateIncrease3(): Attribute
//    {
//        return Attribute::make(
//            get: fn ($value) => $value * 100,
//            set: fn ($value) => $value / 100,
//        );
//    }

    /**
     * @return BelongsTo
     */
    public function logisticsZone(): BelongsTo
    {
        return $this->belongsTo(LogisticsZone::class);
    }
}
