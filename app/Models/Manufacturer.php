<?php

namespace App\Models;

use App\Observers\ManufacturerObserver;
use App\Services\Admin\ShippingService;
use App\Traits\Models\ModelHasAddresses;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

#[ObservedBy([ManufacturerObserver::class])]
class Manufacturer extends Model
{
    use HasFactory, ModelHasAddresses, RevisionableTrait, Userstamps, SoftDeletes;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'country_id',
        'language_id',
        'currency_id',
        'name',
        'logo',
        'place_id',
        'lat',
        'lng',
        'address_line1',
        'address_line2',
        'postal_code',
        'city_id',
        'state_id',
        'country_code',
        'administrative_area',
        'contact_name_1',
        'contact_name_2',
        'phone_1',
        'phone_2',
        'email',
        'billing_email',
        'coc_number',
        'vat_number',
        'iban',
        'bic',
        'timezone',
        'comments',
        'visitor',
        'device_platform',
        'device_type',
        'last_active',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
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
    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    /**
     * @return HasMany
     */
    public function orderQueues(): HasMany
    {
        return $this->hasMany(OrderQueue::class);
    }

    /**
     * @return HasMany
     */
    public function costs(): HasMany
    {
        return $this->hasMany(ManufacturerCost::class);
    }

    /**
     * @return HasMany
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(ManufacturerShipment::class);
    }

    /**
     * @return HasMany
     */
    public function reprints(): HasMany
    {
        return $this->hasMany(Reprint::class);
    }

    public function validateAddress(): void
    {
        $addressData = [
            'name' => $this->contact_name1,
            'company' => $this->name,
            'street1' => $this->address_line1,
            'street2' => $this->address_line2,
            'city' => $this->city->name,
            'state' => $this->state->name,
            'zip' => $this->postal_code,
            'country' => $this->country_code,
            'email' => $this->email ?? $this->billing_email,
        ];

        $response = app(ShippingService::class)->setFromAddress($addressData)->validateAddress('From');
        if (!$response['valid']) {
            $messages = [];
            foreach ($response['messages'] as $message) {
                $messages[] = $message['text'];
            }
            throw new NotFoundHttpException(
                __('Address is not valid with the following messages: :messages ', [
                    'messages' => implode(', ', $messages)
                ])
            );
        }

        if ($response['address_changed']) {
            $this->address_line1 = $response['address']['street1'];
            $this->postal_code = $response['address']['zip'];
            if ($this->country_code !== $response['address']['country']) {
                $country = Country::where('alpha2', $response['address']['country'])->first();
                if ($country) {
                    $this->country_id = $country->id;
                }
            }
            $this->country_code = $response['address']['country'];
            if ($this->state?->name !== $response['address']['state']) {
                $stateName = $response['address']['state'];
                $state = State::firstOrCreate([
                    'name' => $stateName,
                ], [
                    'name' => $stateName,
                    'slug' => Str::slug($stateName),
                    'country_id' => $this->country_id,
                ]);
                $this->state_id = $state->id;
            }
            if ($this->city?->name !== $response['address']['city']) {
                $cityName = $response['address']['city'];
                $city = City::firstOrCreate([
                    'name' => $cityName,
                ], [
                    'name' => $cityName,
                    'slug' => Str::slug($stateName),
                    'state_id' => $this->state_id,
                    'country_id' => $this->country_id,
                ]);
                $this->city_id = $city->id;
            }
        }
    }
}
