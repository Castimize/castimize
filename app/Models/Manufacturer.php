<?php

namespace App\Models;

use App\Traits\Models\ModelHasAddresses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class Manufacturer extends Model
{
    use HasFactory, ModelHasAddresses, RevisionableTrait, Userstamps, SoftDeletes;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'country_id',
        'language_id',
        'currency_id',
        'name',
        'logo',
        'place_id',
        'lat',
        'lng',
        'address_line1',
        'address_line2',
        'house_number',
        'postal_code',
        'city_id',
        'state_id',
        'administrative_area',
        'contact_name_1',
        'contact_name_2',
        'phone_1',
        'phone_2',
        'email',
        'billing_email',
        'coc_number',
        'vat_number',
        'iban',
        'bic',
        'comments',
        'visitor',
        'device_platform',
        'device_type',
        'last_active',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
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
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
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
    public function costs(): HasMany
    {
        return $this->hasMany(ManufacturerCost::class);
    }

    /**
     * @return HasMany
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(ManufacturerShipment::class);
    }
}
