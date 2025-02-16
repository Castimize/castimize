<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class ShopListingModel extends Model
{
    use HasFactory, RevisionableTrait, Userstamps;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_owner_id',
        'shop_owner_auth_id',
        'model_id',
        'shop_listing_id',
        'shop_listing_image_id',
        'state',
    ];

    public function shopOwner(): BelongsTo
    {
        return $this->belongsTo(ShopOwner::class);
    }

    public function shopOwnerAuth(): BelongsTo
    {
        return $this->belongsTo(ShopOwnerAuth::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class);
    }
}
