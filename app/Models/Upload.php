<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'manufacturer_id',
        'currency_id',
        'name',
        'file_name',
        'customer_first_name',
        'customer_last_name',
        'material_name',
        'manufacturer_name',
        'model_volume_cc',
        'model_x_length',
        'model_y_length',
        'model_z_length',
        'model_box_volume',
        'model_surface_area_cm2',
        'model_parts',
        'exceeds_volume',
        'exceeds_number_of_parts',
        'exceeds_file_size',
        'price',
        'currency_code',
        'customer_lead_time',
        'bulk_discount_price',
        'in_queue',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
        'reprint_at',
        'available_for_shipping',
        'in_shipping',
        'manufacturer_shipment_id',
        'customer_shipment_id',
        'in_transit_to_dc',
        'contract_date',
        'arrived_at',
        'manufacturing_costs',
        'model_minimum',
        'bulk_discount_costs',
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
            'in_queue' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'reprint_at' => 'datetime',
            'contract_date' => 'datetime',
            'arrived_at' => 'datetime',
        ];
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
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return BelongsTo
     */
    public function manufacturerShipment(): BelongsTo
    {
        return $this->belongsTo(ManufacturerShipment::class);
    }

    /**
     * @return BelongsTo
     */
    public function customerShipment(): BelongsTo
    {
        return $this->belongsTo(CustomerShipment::class);
    }
}
