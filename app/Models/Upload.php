<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class Upload extends Model
{
    use HasFactory, RevisionableTrait, Userstamps, SoftDeletes;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'material_id',
        'customer_id',
        'currency_id',
        'name',
        'file_name',
        'material_name',
        'model_volume_cc',
        'model_x_length',
        'model_y_length',
        'model_z_length',
        'model_box_volume',
        'model_surface_area_cm2',
        'model_parts',
        'quantity',
        'subtotal',
        'subtotal_tax',
        'total',
        'total_tax',
        'currency_code',
        'customer_lead_time',
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
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
            'meta_data' => AsArrayObject::class,
            'status' => 'string',
        ];
    }

    /**
     * Interact with  subtotal
     */
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  subtotal_tax
     */
    protected function subtotalTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  subtotal
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  subtotal_tax
     */
    protected function totalTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  status
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orderQueue?->status,
        );
    }

    /**
     * Interact with  due_date
     */
    protected function dueDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($this->created_at)->businessDays($this->customer_lead_time, 'add'),
        );
    }

    /**
     * Interact with  completed_at
     */
    protected function completedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->orderQueue?->statuses?->last()->orderStatus?->slug === 'completed' ? $this->orderQueue?->statuses?->last()->orderStatus?->created_at : null,
        );
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return HasOne
     */
    public function  orderQueue(): HasOne
    {
        return $this->hasOne(OrderQueue::class);
    }
}
