<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\Admin\CurrencyEnum;
use App\Models\Country;
use App\Models\LogisticsZone;
use App\Models\Material;
use App\Models\Price;
use App\Models\ShippingFee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PricesApiControllerTest extends TestCase
{
    use DatabaseTransactions;

    // ========================================
    // calculatePrice() tests
    //
    // Public endpoint: no customer-private data, and needed for anonymous
    // visitors configuring a model before they have any account/token.
    // ========================================

    #[Test]
    public function it_calculates_price_without_authentication(): void
    {
        $material = Material::factory()->create(['wp_id' => 123]);
        Price::factory()->create(['material_id' => $material->id]);

        $response = $this->postJson(route('api.api.prices.calculate'), [
            'wp_id' => 123,
            'x_dim' => 1,
            'y_dim' => 1,
            'z_dim' => 1,
            'material_volume' => 1,
            'box_volume' => 1,
            'model_parts' => 1,
            'surface_area' => 1,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['total']]);
    }

    #[Test]
    public function it_returns_404_when_material_not_found_for_calculate_price(): void
    {
        $response = $this->postJson(route('api.api.prices.calculate'), [
            'wp_id' => 99999,
            'x_dim' => 1,
            'y_dim' => 1,
            'z_dim' => 1,
            'material_volume' => 1,
            'box_volume' => 1,
            'model_parts' => 1,
            'surface_area' => 1,
        ]);

        $response->assertNotFound();
    }

    // ========================================
    // calculateShipping() tests
    //
    // Public endpoint, same reasoning as calculatePrice() above.
    // ========================================

    #[Test]
    public function it_calculates_shipping_without_authentication(): void
    {
        $logisticsZone = LogisticsZone::factory()->create();
        ShippingFee::factory()->create(['logistics_zone_id' => $logisticsZone->id]);
        Country::factory()->create(['alpha2' => 'NL', 'logistics_zone_id' => $logisticsZone->id]);

        $response = $this->postJson(route('api.api.prices.calculate.shipping'), [
            'country' => 'NL',
            'currency' => CurrencyEnum::USD->value,
            'uploads' => [],
        ]);

        $response->assertOk();
    }

    #[Test]
    public function it_returns_404_when_country_not_found_for_calculate_shipping(): void
    {
        $response = $this->postJson(route('api.api.prices.calculate.shipping'), [
            'country' => 'XX',
            'uploads' => [],
        ]);

        $response->assertNotFound();
    }
}
