<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\ManufacturerCost;
use App\Models\Material;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManufacturerCostTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $manufacturerCost = new ManufacturerCost;
        $fillable = $manufacturerCost->getFillable();

        $this->assertContains('manufacturer_id', $fillable);
        $this->assertContains('material_id', $fillable);
        $this->assertContains('currency_id', $fillable);
        $this->assertContains('production_lead_time', $fillable);
        $this->assertContains('shipment_lead_time', $fillable);
        $this->assertContains('costs_volume_cc', $fillable);
        $this->assertContains('active', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $manufacturerCost = new ManufacturerCost;
        $casts = $manufacturerCost->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_booleans_correctly(): void
    {
        $manufacturerCost = new ManufacturerCost;
        $casts = $manufacturerCost->getCasts();

        $this->assertEquals('boolean', $casts['setup_fee']);
        $this->assertEquals('boolean', $casts['active']);
    }

    #[Test]
    public function it_converts_costs_volume_cc_from_cents(): void
    {
        $manufacturerCost = new ManufacturerCost;
        $manufacturerCost->setRawAttributes(['costs_volume_cc' => 500]);

        $this->assertEquals(5.00, $manufacturerCost->costs_volume_cc);
    }

    #[Test]
    public function it_converts_costs_minimum_per_stl_from_cents(): void
    {
        $manufacturerCost = new ManufacturerCost;
        $manufacturerCost->setRawAttributes(['costs_minimum_per_stl' => 1000]);

        $this->assertEquals(10.00, $manufacturerCost->costs_minimum_per_stl);
    }

    #[Test]
    public function it_converts_costs_surface_cm2_from_cents(): void
    {
        $manufacturerCost = new ManufacturerCost;
        $manufacturerCost->setRawAttributes(['costs_surface_cm2' => 250]);

        $this->assertEquals(2.50, $manufacturerCost->costs_surface_cm2);
    }

    #[Test]
    public function it_converts_setup_fee_amount_from_cents(): void
    {
        $manufacturerCost = new ManufacturerCost;
        $manufacturerCost->setRawAttributes(['setup_fee_amount' => 1500]);

        $this->assertEquals(15.00, $manufacturerCost->setup_fee_amount);
    }

    #[Test]
    public function it_belongs_to_manufacturer(): void
    {
        $manufacturerCost = new ManufacturerCost;

        $this->assertInstanceOf(BelongsTo::class, $manufacturerCost->manufacturer());
        $this->assertEquals(Manufacturer::class, $manufacturerCost->manufacturer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_material(): void
    {
        $manufacturerCost = new ManufacturerCost;

        $this->assertInstanceOf(BelongsTo::class, $manufacturerCost->material());
        $this->assertEquals(Material::class, $manufacturerCost->material()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $manufacturerCost = new ManufacturerCost;

        $this->assertInstanceOf(BelongsTo::class, $manufacturerCost->currency());
        $this->assertEquals(Currency::class, $manufacturerCost->currency()->getRelated()::class);
    }
}
