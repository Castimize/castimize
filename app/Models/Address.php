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
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;
    use Userstamps;

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

    public function getFullAddressAttribute(): ?string
    {
        return $this->address_line1.' '.$this->address_line2.', '.$this->postal_code.' '.$this->city->name.', '.$this->country->name;
    }

    public function getFullAddressWithBreaksAttribute(): ?string
    {
        return $this->address_line1.'<br>'.$this->address_line2.'<br>'.$this->postal_code.' '.$this->city->name.'<br>'.$this->country->name;
    }

    public function getFullAddressWithNewLinesAttribute(): ?string
    {
        return $this->address_line1."\n".$this->address_line2."\n".$this->postal_code.' '.$this->city->name."\n".$this->country->name;
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

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
                'company',
                'contact_name',
                'phone',
                'email',
            ]);
    }
}
