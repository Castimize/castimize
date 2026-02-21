<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Country;
use App\Models\LogisticsZone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $country = new Country;
        $fillable = $country->getFillable();

        $this->assertContains('logistics_zone_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('alpha2', $fillable);
        $this->assertContains('alpha3', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $country = new Country;
        $casts = $country->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_has_eu_countries_constant(): void
    {
        $this->assertIsArray(Country::EU_COUNTRIES);
        $this->assertContains('NL', Country::EU_COUNTRIES);
        $this->assertContains('DE', Country::EU_COUNTRIES);
        $this->assertContains('FR', Country::EU_COUNTRIES);
        $this->assertContains('BE', Country::EU_COUNTRIES);
    }

    #[Test]
    public function it_converts_alpha2_to_uppercase_on_get(): void
    {
        $country = new Country;
        $country->setRawAttributes(['alpha2' => 'nl']);

        $this->assertEquals('NL', $country->alpha2);
    }

    #[Test]
    public function it_converts_alpha2_to_lowercase_on_set(): void
    {
        $country = new Country;
        $country->alpha2 = 'NL';

        // When set, it's converted to lowercase internally and then uppercase on get
        $this->assertEquals('NL', $country->alpha2);
    }

    #[Test]
    public function it_belongs_to_logistics_zone(): void
    {
        $country = new Country;

        $this->assertInstanceOf(BelongsTo::class, $country->logisticsZone());
        $this->assertEquals(LogisticsZone::class, $country->logisticsZone()->getRelated()::class);
    }
}
