<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Country;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class OrdersApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions(['viewOrder']);
    }

    // ========================================
    // show() tests
    // ========================================

    #[Test]
    public function it_returns_order_by_order_number(): void
    {
        $order = Order::factory()->create(['order_number' => 12345]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.orders.show', ['order_number' => 12345]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'order_number',
            ],
        ]);
        $response->assertJsonPath('data.order_number', 12345);
    }

    #[Test]
    public function it_returns_404_when_order_not_found(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.orders.show', ['order_number' => 99999]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_403_when_user_lacks_permission(): void
    {
        $order = Order::factory()->create(['order_number' => 11111]);

        $userWithoutPermission = User::factory()->create();
        Sanctum::actingAs($userWithoutPermission);

        $response = $this->getJson(route('api.api.orders.show', ['order_number' => 11111]));

        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_401_when_not_authenticated(): void
    {
        $response = $this->getJson(route('api.api.orders.show', ['order_number' => 12345]));

        $response->assertUnauthorized();
    }

    // ========================================
    // calculateExpectedDeliveryDate() tests
    // ========================================

    #[Test]
    public function it_calculates_expected_delivery_date(): void
    {
        // Ensure country exists
        Country::firstOrCreate(
            ['alpha2' => 'NL'],
            [
                'name' => 'Netherlands',
                'alpha3' => 'NLD',
                'numeric' => 528,
            ]
        );

        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.orders.calculate-expected-delivery-date'), [
            'country' => 'NL',
            'uploads' => [],
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'expected_delivery_date',
        ]);
        $response->assertJsonPath('success', true);
    }

    #[Test]
    public function it_handles_uploads_as_json_string(): void
    {
        Country::firstOrCreate(
            ['alpha2' => 'DE'],
            [
                'name' => 'Germany',
                'alpha3' => 'DEU',
                'numeric' => 276,
            ]
        );

        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.orders.calculate-expected-delivery-date'), [
            'country' => 'DE',
            'uploads' => json_encode([]),
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
    }
}
