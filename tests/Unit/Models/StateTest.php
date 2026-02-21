<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StateTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $state = new State;
        $fillable = $state->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('short_name', $fillable);
        $this->assertContains('country_id', $fillable);
        $this->assertContains('slug', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $state = new State;
        $casts = $state->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_belongs_to_country(): void
    {
        $state = new State;

        $this->assertInstanceOf(BelongsTo::class, $state->country());
        $this->assertEquals(Country::class, $state->country()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_cities(): void
    {
        $state = new State;

        $this->assertInstanceOf(HasMany::class, $state->cities());
        $this->assertEquals(City::class, $state->cities()->getRelated()::class);
    }
}
