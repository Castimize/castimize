<?php

namespace App\Models;

use App\Enums\Admin\PaymentFeeTypesEnum;
use App\Observers\PaymentFeeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([PaymentFeeObserver::class])]
class PaymentFee extends Model
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
        'currency_id',
        'payment_method',
        'type',
        'fee',
        'minimum_fee',
        'maximum_fee',
        'currency_code',
    ];

    /**
     * Interact with fee
     */
    protected function fee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->type === PaymentFeeTypesEnum::FIXED->value ? $value / 100 : $value * 100,
            set: fn ($value) => $this->type === PaymentFeeTypesEnum::FIXED->value ? $value * 100 : $value / 100,
        );
    }

    /**
     * Interact with minimum_fee
     */
    protected function minimumFee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->type === PaymentFeeTypesEnum::FIXED->value ? $value / 100 : $value * 100,
            set: fn ($value) => $this->type === PaymentFeeTypesEnum::FIXED->value ? $value * 100 : $value / 100,
        );
    }

    /**
     * Interact with maximum_fee
     */
    protected function maximumFee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->type === PaymentFeeTypesEnum::FIXED->value ? $value / 100 : $value * 100,
            set: fn ($value) => $this->type === PaymentFeeTypesEnum::FIXED->value ? $value * 100 : $value / 100,
        );
    }
}
