<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Material;
use App\Models\MaterialGroup;
use App\Models\Price;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaterialTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $material = new Material;
        $fillable = $material->getFillable();

        $this->assertContains('material_group_id', $fillable);
        $this->assertContains('currency_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('discount', $fillable);
        $this->assertContains('dc_lead_time', $fillable);
        $this->assertContains('hs_code', $fillable);
        $this->assertContains('density', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $material = new Material;
        $casts = $material->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_floats_correctly(): void
    {
        $material = new Material;
        $casts = $material->getCasts();

        $this->assertEquals('float', $casts['minimum_x_length']);
        $this->assertEquals('float', $casts['maximum_x_length']);
        $this->assertEquals('float', $casts['minimum_volume']);
        $this->assertEquals('float', $casts['maximum_volume']);
    }

    #[Test]
    public function it_converts_fast_delivery_fee_from_cents(): void
    {
        $material = new Material;
        $material->setRawAttributes(['fast_delivery_fee' => 1500]);

        $this->assertEquals(15.00, $material->fast_delivery_fee);
    }

    #[Test]
    public function it_converts_discount_to_percentage(): void
    {
        $material = new Material;
        $material->setRawAttributes(['discount' => 0.10]);

        $this->assertEquals(10.0, $material->discount);
    }

    #[Test]
    public function it_converts_bulk_discount_10_to_percentage(): void
    {
        $material = new Material;
        $material->setRawAttributes(['bulk_discount_10' => 0.05]);

        $this->assertEquals(5.0, $material->bulk_discount_10);
    }

    #[Test]
    public function it_converts_bulk_discount_25_to_percentage(): void
    {
        $material = new Material;
        $material->setRawAttributes(['bulk_discount_25' => 0.10]);

        $this->assertEquals(10.0, $material->bulk_discount_25);
    }

    #[Test]
    public function it_converts_bulk_discount_50_to_percentage(): void
    {
        $material = new Material;
        $material->setRawAttributes(['bulk_discount_50' => 0.15]);

        $this->assertEquals(15.0, $material->bulk_discount_50);
    }

    #[Test]
    public function it_belongs_to_material_group(): void
    {
        $material = new Material;

        $this->assertInstanceOf(BelongsTo::class, $material->materialGroup());
        $this->assertEquals(MaterialGroup::class, $material->materialGroup()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $material = new Material;

        $this->assertInstanceOf(BelongsTo::class, $material->currency());
        $this->assertEquals(Currency::class, $material->currency()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_prices(): void
    {
        $material = new Material;

        $this->assertInstanceOf(HasMany::class, $material->prices());
        $this->assertEquals(Price::class, $material->prices()->getRelated()::class);
    }
}
