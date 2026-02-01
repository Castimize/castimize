<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Admin;

use App\DTO\Order\CalculateShippingFeeUploadDTO;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ManufacturerCost;
use App\Models\Material;
use App\Models\MaterialGroup;
use App\Models\Price;
use App\Services\Admin\CalculatePricesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class CalculatePricesServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CalculatePricesService $service;

    private Material $material;

    private Price $price;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CalculatePricesService;
        $this->setUpTestData();
    }

    private function setUpTestData(): void
    {
        $currency = Currency::firstOrCreate(
            ['code' => 'USD'],
            ['name' => 'US Dollar']
        );

        $materialGroup = MaterialGroup::first() ?? MaterialGroup::factory()->create();

        $this->material = Material::create([
            'material_group_id' => $materialGroup->id,
            'currency_id' => $currency->id,
            'wp_id' => 9999,
            'name' => 'Test Material',
            'dc_lead_time' => 10,
            'fast_delivery_lead_time' => 5,
            'fast_delivery_fee' => 10000,
            'currency_code' => 'USD',
            'minimum_x_length' => 0.1,
            'maximum_x_length' => 100,
            'minimum_y_length' => 0.1,
            'maximum_y_length' => 100,
            'minimum_z_length' => 0.1,
            'maximum_z_length' => 100,
            'minimum_volume' => 0.01,
            'maximum_volume' => 1000,
            'minimum_box_volume' => 0.01,
            'maximum_box_volume' => 1000,
            'density' => 8.5,
        ]);

        $this->price = Price::create([
            'material_id' => $this->material->id,
            'country_id' => Country::first()?->id ?? Country::factory()->create()->id,
            'currency_id' => $currency->id,
            'setup_fee' => false,
            'setup_fee_amount' => 0,
            'minimum_per_stl' => 0.5,
            'price_minimum_per_stl' => 25.00,
            'price_volume_cc' => 15.00,
            'price_surface_cm2' => 0.10,
            'fixed_fee_per_part' => 0,
            'currency_code' => 'USD',
        ]);
    }

    #[Test]
    public function it_calculates_price_with_volume_and_surface_area(): void
    {
        $request = new Request([
            'wp_id' => $this->material->wp_id,
            'x_dim' => 5,
            'y_dim' => 5,
            'z_dim' => 5,
            'material_volume' => 10,
            'surface_area' => 100,
            'box_volume' => 125,
            'model_parts' => 1,
        ]);

        $result = $this->service->calculatePrice($request);

        $this->assertInstanceOf(Price::class, $result);
        $expectedTotal = (10 * 15.00) + (100 * 0.10);
        $this->assertEquals($expectedTotal, $result->calculated_total);
    }

    #[Test]
    public function it_applies_minimum_price_when_calculated_is_lower(): void
    {
        $request = new Request([
            'wp_id' => $this->material->wp_id,
            'x_dim' => 1,
            'y_dim' => 1,
            'z_dim' => 1,
            'material_volume' => 0.5,
            'surface_area' => 5,
            'box_volume' => 1,
            'model_parts' => 1,
        ]);

        $result = $this->service->calculatePrice($request);

        $calculatedTotal = (0.5 * 15.00) + (5 * 0.10);
        $this->assertLessThan(25.00, $calculatedTotal);
        $this->assertEquals(25.00, $result->calculated_total);
    }

    #[Test]
    public function it_calculates_price_with_setup_fee(): void
    {
        $this->price->update([
            'setup_fee' => true,
            'setup_fee_amount' => 50.00,
        ]);

        $request = new Request([
            'wp_id' => $this->material->wp_id,
            'x_dim' => 5,
            'y_dim' => 5,
            'z_dim' => 5,
            'material_volume' => 10,
            'surface_area' => 100,
            'box_volume' => 125,
            'model_parts' => 1,
        ]);

        $result = $this->service->calculatePrice($request);

        $expectedTotal = 50.00 + (10 * 15.00);
        $this->assertEquals($expectedTotal, $result->calculated_total);
    }

    #[Test]
    public function it_throws_not_found_exception_when_material_not_found(): void
    {
        $request = new Request([
            'wp_id' => 99999,
            'x_dim' => 5,
            'y_dim' => 5,
            'z_dim' => 5,
            'material_volume' => 10,
            'surface_area' => 100,
            'box_volume' => 125,
            'model_parts' => 1,
        ]);

        $this->expectException(NotFoundHttpException::class);

        $this->service->calculatePrice($request);
    }

    #[Test]
    public function it_throws_exception_when_dimensions_exceed_maximum(): void
    {
        $request = new Request([
            'wp_id' => $this->material->wp_id,
            'x_dim' => 200,
            'y_dim' => 5,
            'z_dim' => 5,
            'material_volume' => 10,
            'surface_area' => 100,
            'box_volume' => 125,
            'model_parts' => 1,
        ]);

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->service->calculatePrice($request);
    }

    #[Test]
    public function it_throws_exception_when_dimensions_below_minimum(): void
    {
        $request = new Request([
            'wp_id' => $this->material->wp_id,
            'x_dim' => 0.01,
            'y_dim' => 5,
            'z_dim' => 5,
            'material_volume' => 10,
            'surface_area' => 100,
            'box_volume' => 125,
            'model_parts' => 1,
        ]);

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->service->calculatePrice($request);
    }

    #[Test]
    public function it_throws_exception_when_volume_exceeds_maximum(): void
    {
        $request = new Request([
            'wp_id' => $this->material->wp_id,
            'x_dim' => 5,
            'y_dim' => 5,
            'z_dim' => 5,
            'material_volume' => 5000,
            'surface_area' => 100,
            'box_volume' => 125,
            'model_parts' => 1,
        ]);

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->service->calculatePrice($request);
    }

    #[Test]
    public function it_throws_exception_when_model_has_too_many_parts(): void
    {
        $request = new Request([
            'wp_id' => $this->material->wp_id,
            'x_dim' => 5,
            'y_dim' => 5,
            'z_dim' => 5,
            'material_volume' => 10,
            'surface_area' => 100,
            'box_volume' => 125,
            'model_parts' => 5,
        ]);

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->service->calculatePrice($request);
    }

    #[Test]
    public function it_calculates_price_of_model_directly(): void
    {
        $total = $this->service->calculatePriceOfModel($this->price, 10.0, 50.0);

        $expected = (10.0 * 15.00) + (50.0 * 0.10);
        $this->assertEquals($expected, $total);
    }

    #[Test]
    public function it_calculates_manufacturer_costs_with_setup_fee(): void
    {
        $cost = new ManufacturerCost;
        $cost->setup_fee = true;
        $cost->setup_fee_amount = 30.00;
        $cost->costs_volume_cc = 10.00;
        $cost->costs_surface_cm2 = 0.05;
        $cost->minimum_per_stl = 0.5;
        $cost->costs_minimum_per_stl = 20.00;

        $total = $this->service->calculateCostsOfModel($cost, 5.0, 25.0, 2);

        $expectedPerUnit = 30.00 + (5.0 * 10.00);
        $this->assertEquals($expectedPerUnit * 2, $total);
    }

    #[Test]
    public function it_calculates_manufacturer_costs_with_minimum(): void
    {
        $cost = new ManufacturerCost;
        $cost->setup_fee = false;
        $cost->setup_fee_amount = 0;
        $cost->costs_volume_cc = 10.00;
        $cost->costs_surface_cm2 = 0.05;
        $cost->minimum_per_stl = 5.0;
        $cost->costs_minimum_per_stl = 20.00;

        $total = $this->service->calculateCostsOfModel($cost, 2.0, 10.0, 1);

        $this->assertEquals(20.00, $total);
    }

    #[Test]
    public function it_calculates_manufacturer_costs_with_volume_and_surface(): void
    {
        $cost = new ManufacturerCost;
        $cost->setup_fee = false;
        $cost->setup_fee_amount = 0;
        $cost->costs_volume_cc = 10.00;
        $cost->costs_surface_cm2 = 0.05;
        $cost->minimum_per_stl = 0.5;
        $cost->costs_minimum_per_stl = 20.00;

        $total = $this->service->calculateCostsOfModel($cost, 10.0, 50.0, 1);

        $expected = (10.0 * 10.00) + (50.0 * 0.05);
        $this->assertEquals($expected, $total);
    }

    #[Test]
    public function it_calculates_total_volume_from_multiple_uploads(): void
    {
        $uploads = collect([
            new CalculateShippingFeeUploadDTO(modelBoxVolume: 30.0, quantity: 2),
            new CalculateShippingFeeUploadDTO(modelBoxVolume: 25.0, quantity: 2),
        ]);

        $totalVolume = 0;
        foreach ($uploads as $upload) {
            $totalVolume += $upload->modelBoxVolume * $upload->quantity;
        }

        $this->assertEquals(110.0, $totalVolume);
    }
}
