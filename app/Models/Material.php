<?php

namespace App\Models;

use App\Observers\MaterialObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([MaterialObserver::class])]
class Material extends Model
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
        'material_group_id',
        'currency_id',
        'wp_id',
        'name',
        'discount',
        'bulk_discount_10',
        'bulk_discount_25',
        'bulk_discount_50',
        'customer_lead_time',
        'fast_delivery_lead_time',
        'fast_delivery_fee',
        'currency_code',
        'hs_code_description',
        'hs_code',
        'article_eu_description',
        'article_us_description',
        'tariff_code_eu',
        'tariff_code_us',
        'minimum_x_length',
        'maximum_x_length',
        'minimum_y_length',
        'maximum_y_length',
        'minimum_z_length',
        'maximum_z_length',
        'minimum_volume',
        'maximum_volume',
        'material_min_bounding_box',
        'material_max_bounding_box',
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
     * Interact with fast delivery fee
     */
    protected function fastDeliveryFee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * @return BelongsTo
     */
    public function materialGroup(): BelongsTo
    {
        return $this->belongsTo(MaterialGroup::class);
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
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
}
