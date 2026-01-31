<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer;
use App\Models\State;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $address = new Address;
        $fillable = $address->getFillable();

        $this->assertContains('place_id', $fillable);
        $this->assertContains('lat', $fillable);
        $this->assertContains('lng', $fillable);
        $this->assertContains('address_line1', $fillable);
        $this->assertContains('address_line2', $fillable);
        $this->assertContains('postal_code', $fillable);
        $this->assertContains('city_id', $fillable);
        $this->assertContains('state_id', $fillable);
        $this->assertContains('country_id', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $address = new Address;
        $casts = $address->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_belongs_to_city(): void
    {
        $address = new Address;

        $this->assertInstanceOf(BelongsTo::class, $address->city());
        $this->assertEquals(City::class, $address->city()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_state(): void
    {
        $address = new Address;

        $this->assertInstanceOf(BelongsTo::class, $address->state());
        $this->assertEquals(State::class, $address->state()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_country(): void
    {
        $address = new Address;

        $this->assertInstanceOf(BelongsTo::class, $address->country());
        $this->assertEquals(Country::class, $address->country()->getRelated()::class);
    }

    #[Test]
    public function it_has_morph_to_many_customers(): void
    {
        $address = new Address;

        $this->assertInstanceOf(MorphToMany::class, $address->customers());
        $this->assertEquals(Customer::class, $address->customers()->getRelated()::class);
    }
}
