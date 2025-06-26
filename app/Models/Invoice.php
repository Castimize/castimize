<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class Invoice extends Model
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
        'invoice_number',
        'invoice_date',
        'exact_online_guid',
        'debit',
        'total',
        'total_tax',
        'currency_code',
        'description',
        'email',
        'email_cc',
        'contact_person',
        'address_line1',
        'address_line2',
        'postal_code',
        'city',
        'country',
        'vat',
        'iban',
        'bic',
        'vat_number',
        'sent',
        'sent_at',
        'paid',
        'paid_at',
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
            'invoice_date' => 'datetime',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'sent' => 'boolean',
            'paid' => 'boolean',
            'meta_data' => AsArrayObject::class,
        ];
    }

    /**
     * Interact with  total
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
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return HasMany
     */
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function exactSalesEntries(): HasMany
    {
        return $this->hasMany(InvoiceExactSalesEntry::class);
    }
}
