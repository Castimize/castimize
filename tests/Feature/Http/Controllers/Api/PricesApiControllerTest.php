<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class PricesApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions(['viewPricing']);
    }

    // ========================================
    // calculatePrice() tests
    // ========================================

    #[Test]
    public function it_returns_403_when_user_lacks_permission_for_calculate_price(): void
    {
        $userWithoutPermission = User::factory()->create();
        Sanctum::actingAs($userWithoutPermission);

        $response = $this->postJson(route('api.api.prices.calculate'), [
            'material_id' => 1,
            'model_volume_cc' => 10.5,
            'model_surface_area_cm2' => 20.5,
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_401_when_not_authenticated_for_calculate_price(): void
    {
        $response = $this->postJson(route('api.api.prices.calculate'), [
            'material_id' => 1,
            'model_volume_cc' => 10.5,
            'model_surface_area_cm2' => 20.5,
        ]);

        $response->assertUnauthorized();
    }

    // ========================================
    // calculateShipping() tests
    // ========================================

    #[Test]
    public function it_returns_403_when_user_lacks_permission_for_calculate_shipping(): void
    {
        $userWithoutPermission = User::factory()->create();
        Sanctum::actingAs($userWithoutPermission);

        $response = $this->postJson(route('api.api.prices.calculate.shipping'), [
            'country' => 'NL',
            'uploads' => [],
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_401_when_not_authenticated_for_calculate_shipping(): void
    {
        $response = $this->postJson(route('api.api.prices.calculate.shipping'), [
            'country' => 'NL',
            'uploads' => [],
        ]);

        $response->assertUnauthorized();
    }
}
