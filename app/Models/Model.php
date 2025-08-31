<?php

namespace App\Models;

use App\Observers\ModelObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy(ModelObserver::class)]
class Model extends EloquentModel
{
    use HasFactory, RevisionableTrait, SoftDeletes, Userstamps;

    protected $revisionForceDeleteEnabled = true;

    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'material_id',
        'model_name',
        'name',
        'file_name',
        'thumb_name',
        'model_volume_cc',
        'model_x_length',
        'model_y_length',
        'model_z_length',
        'model_surface_area_cm2',
        'model_parts',
        'model_box_volume',
        'model_scale',
        'categories',
        'meta_data',
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
            'categories' => 'json',
            'meta_data' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class);
    }

    public function shopListingModel(): HasOne
    {
        return $this->hasOne(ShopListingModel::class);
    }
}
