<?php

namespace App\Models;

use App\Observers\CustomerObserver;
use App\Traits\Models\ModelHasAddresses;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([CustomerObserver::class])]
class Customer extends Model
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
        'wp_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'comments',
        'visitor',
        'device_platform',
        'device_type',
        'last_active',
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
            'last_active' => 'datetime',
        ];
    }

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
     * @return HasMany
     */
    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany
     */
    public function models(): HasMany
    {
        return $this->hasMany(\App\Models\Model::class);
    }

    /**
     * @return HasMany
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(CustomerShipment::class);
    }

    /**
     * @return HasMany
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
