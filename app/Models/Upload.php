<?php

namespace App\Models;

use App\Observers\UploadObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([UploadObserver::class])]
class Upload extends Model
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
        'wp_id',
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
        'total_refund',
        'total_refund_tax',
        'manufacturer_discount',
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
        ];
    }

    public function getLastStatus(): mixed
    {
        return $this->orderQueue?->getLastStatus();
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
     * Interact with  otal
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  total_tax
     */
    protected function totalTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  total_refund
     */
    protected function totalRefund(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with  total_refund_tax
     */
    protected function totalRefundTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Interact with manufacturer_discount
     */
    protected function manufacturerDiscount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value * 100,
            set: fn ($value) => $value / 100,
        );
    }

    /**
     * Interact with  manufacturer_costs
     */
    protected function manufacturerCosts(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orderQueue->manufacturer_costs,
        );
    }

    /**
     * Interact with  profit
     */
    protected function profit(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->total - $this->orderQueue->manufacturer_costs) / 100,
        );
    }

    /**
     * Interact with  status
     */
    protected function statusSlug(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getLastStatus()?->slug,
        );
    }

    /**
     * Interact with  status
     */
    protected function statusCreatedAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getLastStatus()?->created_at ?? $this->created_at,
        );
    }

    /**
     * Interact with  due_date
     */
    protected function dueDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($this->created_at)->addBusinessDays($this->customer_lead_time),
        );
    }

    /**
     * Interact with  completed_at
     */
    protected function completedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->status_slug === 'completed' ? $this->status_created_at : null,
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderQueue(): HasOne
    {
        return $this->hasOne(OrderQueue::class)->latestOfMany();
    }

    public function orderQueues(): HasMany
    {
        return $this->hasMany(OrderQueue::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
