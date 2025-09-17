<?php

namespace App\Models;

use App\Observers\OrderQueueStatusObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([OrderQueueStatusObserver::class])]
class OrderQueueStatus extends Model
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
        'order_queue_id',
        'order_status_id',
        'status',
        'slug',
        'target_date',
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
            'target_date' => 'datetime',
        ];
    }

    public function orderQueue(): BelongsTo
    {
        return $this->belongsTo(OrderQueue::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }
}
