<?php

namespace App\Models;

use App\Observers\RejectionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([RejectionObserver::class])]
class Rejection extends Model
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
        'upload_id',
        'rejection_reason_id',
        'reason_manufacturer',
        'note_manufacturer',
        'note_castimize',
        'photo',
        'approved_at',
        'declined_at',
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
            'approved_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    /**
     * Interact with  order_number
     */
    protected function orderNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->order->order_number,
        );
    }

    /**
     * Interact with  order_date
     */
    protected function orderDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->order->created_at,
        );
    }

    /**
     * Interact with  material_name
     */
    protected function materialName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->upload->material->name,
        );
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

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function rejectionReason(): BelongsTo
    {
        return $this->belongsTo(RejectionReason::class);
    }
}
