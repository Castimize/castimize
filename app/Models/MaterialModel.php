<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialModel extends Model
{
    use HasFactory;

    protected $table = 'material_model';

    protected $primaryKey = ['model_id', 'material_id'];

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
