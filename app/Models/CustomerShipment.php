<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class CustomerShipment extends Model
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
        'customer_id',
        'currency_id',
        'sent_at',
        'arrived_at',
        'time_in_transit', //date difference created_at en arrived_at. indien geen arrived_at dan date difference created_at en today()
        'expected_delivery_date',
        'ups_tracking',
        'ups_tracking_manual',
        'amount',
        'type',
        'ups_service',
        'service_lead_time',
        'service_costs',
        'currency_code',
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
            'sent_at' => 'datetime',
            'arrived_at' => 'datetime',
            'expected_delivery_date' => 'datetime',
            'service_lead_time' => 'datetime',
            'ups_service'=> 'boolean',
        ];
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
