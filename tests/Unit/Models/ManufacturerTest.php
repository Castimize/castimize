<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Manufacturer;
use App\Models\ManufacturerCost;
use App\Models\ManufacturerShipment;
use App\Models\OrderQueue;
use App\Models\Reprint;
use App\Models\State;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManufacturerTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $manufacturer = new Manufacturer;
        $fillable = $manufacturer->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('country_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('address_line1', $fillable);
        $this->assertContains('postal_code', $fillable);
        $this->assertContains('city_id', $fillable);
        $this->assertContains('timezone', $fillable);
    }

    #[Test]
    public function it_belongs_to_user(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(BelongsTo::class, $manufacturer->user());
        $this->assertEquals(User::class, $manufacturer->user()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_city(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(BelongsTo::class, $manufacturer->city());
        $this->assertEquals(City::class, $manufacturer->city()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_state(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(BelongsTo::class, $manufacturer->state());
        $this->assertEquals(State::class, $manufacturer->state()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_country(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(BelongsTo::class, $manufacturer->country());
        $this->assertEquals(Country::class, $manufacturer->country()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_language(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(BelongsTo::class, $manufacturer->language());
        $this->assertEquals(Language::class, $manufacturer->language()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(BelongsTo::class, $manufacturer->currency());
        $this->assertEquals(Currency::class, $manufacturer->currency()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_uploads(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(HasMany::class, $manufacturer->uploads());
        $this->assertEquals(Upload::class, $manufacturer->uploads()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_order_queues(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(HasMany::class, $manufacturer->orderQueues());
        $this->assertEquals(OrderQueue::class, $manufacturer->orderQueues()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_costs(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(HasMany::class, $manufacturer->costs());
        $this->assertEquals(ManufacturerCost::class, $manufacturer->costs()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_shipments(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(HasMany::class, $manufacturer->shipments());
        $this->assertEquals(ManufacturerShipment::class, $manufacturer->shipments()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_reprints(): void
    {
        $manufacturer = new Manufacturer;

        $this->assertInstanceOf(HasMany::class, $manufacturer->reprints());
        $this->assertEquals(Reprint::class, $manufacturer->reprints()->getRelated()::class);
    }
}
