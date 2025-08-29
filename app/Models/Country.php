<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class Country extends Model
{
    use HasFactory, RevisionableTrait, SoftDeletes, Userstamps;

    public const EU_COUNTRIES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'DE',
        'DK',
        'ES',
        'EE',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LV',
        'LT',
        'LU',
        'MT',
        'NL',
        'PL',
        'PT',
        'CZ',
        'RO',
        'GB',
        'SK',
        'SI',
        'SE',
    ];

    protected $revisionForceDeleteEnabled = true;

    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'logistics_zone_id',
        'name',
        'alpha2',
        'alpha3',
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
     * Interact with  on_schedule
     */
    protected function alpha2(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? strtoupper($value) : '',
            set: fn ($value) => strtolower($value),
        );
    }

    public function logisticsZone(): BelongsTo
    {
        return $this->belongsTo(LogisticsZone::class);
    }
}
