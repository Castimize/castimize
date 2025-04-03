<?php

namespace App\Models;

use App\Observers\ShopOwnerAuthObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy(ShopOwnerAuthObserver::class)]
class Shop extends Model
{
    use HasFactory, SoftDeletes, RevisionableTrait, Userstamps;

    public $oathKey;
    public $oathSecret;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected  $fillable = [
        'shop_owner_id',
        'shop',
        'shop_oauth',
        'active',
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
            'shop_oauth' => 'array',
            'active' => 'boolean',
        ];
    }

    public function shopOwner(): BelongsTo
    {
        return $this->belongsTo(ShopOwner::class);
    }

    public function shopListingModels(): HasMany
    {
        return $this->hasMany(ShopListingModel::class);
    }
}
