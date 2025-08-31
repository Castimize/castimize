<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class Reprint extends Model
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
        'manufacturer_id',
        'order_queue_id',
        'order_id',
        'reprint_culprit_id',
        'reprint_reason_id',
        'reason',
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

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function orderQueue(): BelongsTo
    {
        return $this->belongsTo(OrderQueue::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reprintCulprit(): BelongsTo
    {
        return $this->belongsTo(ReprintCulprit::class);
    }

    public function reprintReason(): BelongsTo
    {
        return $this->belongsTo(ReprintReason::class);
    }
}
