<?php

namespace App\Models;

use App\Observers\MaterialModelObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[ObservedBy(MaterialModelObserver::class)]
class MaterialModel extends Pivot
{
    use HasFactory;

    protected $table = 'material_model';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model_id',
        'material_id',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
