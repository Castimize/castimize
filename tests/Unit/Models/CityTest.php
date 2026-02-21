<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CityTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $city = new City;
        $fillable = $city->getFillable();

        $this->assertContains('place_id', $fillable);
        $this->assertContains('lat', $fillable);
        $this->assertContains('lng', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('state_id', $fillable);
        $this->assertContains('country_id', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $city = new City;
        $casts = $city->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_belongs_to_state(): void
    {
        $city = new City;

        $this->assertInstanceOf(BelongsTo::class, $city->state());
        $this->assertEquals(State::class, $city->state()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_country(): void
    {
        $city = new City;

        $this->assertInstanceOf(BelongsTo::class, $city->country());
        $this->assertEquals(Country::class, $city->country()->getRelated()::class);
    }
}
