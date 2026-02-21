<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Material;
use App\Models\Price;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PriceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $price = new Price;
        $fillable = $price->getFillable();

        $this->assertContains('material_id', $fillable);
        $this->assertContains('country_id', $fillable);
        $this->assertContains('currency_id', $fillable);
        $this->assertContains('setup_fee', $fillable);
        $this->assertContains('setup_fee_amount', $fillable);
        $this->assertContains('price_volume_cc', $fillable);
        $this->assertContains('price_surface_cm2', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $price = new Price;
        $casts = $price->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_setup_fee_as_boolean(): void
    {
        $price = new Price;
        $casts = $price->getCasts();

        $this->assertEquals('boolean', $casts['setup_fee']);
    }

    #[Test]
    public function it_converts_setup_fee_amount_from_cents(): void
    {
        $price = new Price;
        $price->setRawAttributes(['setup_fee_amount' => 1000]);

        $this->assertEquals(10.00, $price->setup_fee_amount);
    }

    #[Test]
    public function it_converts_price_volume_cc_from_cents(): void
    {
        $price = new Price;
        $price->setRawAttributes(['price_volume_cc' => 500]);

        $this->assertEquals(5.00, $price->price_volume_cc);
    }

    #[Test]
    public function it_converts_price_surface_cm2_from_cents(): void
    {
        $price = new Price;
        $price->setRawAttributes(['price_surface_cm2' => 250]);

        $this->assertEquals(2.50, $price->price_surface_cm2);
    }

    #[Test]
    public function it_converts_fixed_fee_per_part_from_cents(): void
    {
        $price = new Price;
        $price->setRawAttributes(['fixed_fee_per_part' => 100]);

        $this->assertEquals(1.00, $price->fixed_fee_per_part);
    }

    #[Test]
    public function it_converts_material_discount_from_cents(): void
    {
        $price = new Price;
        $price->setRawAttributes(['material_discount' => 1500]);

        $this->assertEquals(15.00, $price->material_discount);
    }

    #[Test]
    public function it_converts_bulk_discount_from_cents(): void
    {
        $price = new Price;
        $price->setRawAttributes(['bulk_discount' => 2000]);

        $this->assertEquals(20.00, $price->bulk_discount);
    }

    #[Test]
    public function it_belongs_to_material(): void
    {
        $price = new Price;

        $this->assertInstanceOf(BelongsTo::class, $price->material());
        $this->assertEquals(Material::class, $price->material()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_country(): void
    {
        $price = new Price;

        $this->assertInstanceOf(BelongsTo::class, $price->country());
        $this->assertEquals(Country::class, $price->country()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $price = new Price;

        $this->assertInstanceOf(BelongsTo::class, $price->currency());
        $this->assertEquals(Currency::class, $price->currency()->getRelated()::class);
    }
}
