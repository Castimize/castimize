<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class Address extends Model
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
        'place_id',
        'lat',
        'lng',
        'address_line1',
        'address_line2',
        'postal_code',
        'city_id',
        'state_id',
        'administrative_area',
        'country_id',
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
     * @return null|string
     */
    public function getFullAddressAttribute(): ?string
    {
        return $this->address_line1 . ' ' . $this->address_line2 . ', ' . $this->postal_code . ' ' . $this->city->name . ', ' . $this->country->name;
    }

    /**
     * @return null|string
     */
    public function getFullAddressWithBreaksAttribute(): ?string
    {
        return $this->address_line1 . "<br>" . $this->address_line2 . "<br>" . $this->postal_code . ' ' . $this->city->name . "<br>" . $this->country->name;
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
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return MorphToMany
     */
    public function customers(): MorphToMany
    {
        return $this->morphedByMany(
            Customer::class,
            'model',
            'model_has_addresses'
        )
        ->withPivot([
            'default_billing',
            'default_shipping',
            'contact_name',
            'phone',
            'email',
        ]);
    }
}
