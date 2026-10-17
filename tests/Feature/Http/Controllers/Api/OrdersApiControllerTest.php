<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
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
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }

    /**
     * Create the customer-role User account a customer-scoped token belongs
     * to, and return a plain-text token for it.
     */
    private function createCustomerApiToken(Customer $customer): string
    {
        $user = User::factory()->create(['wp_id' => $customer->wp_id]);
        $user->assignRole('customer');

        return $user->createToken('test-token')->plainTextToken;
    }

    // ========================================
    // show() tests
    // ========================================

    #[Test]
    public function it_returns_order_by_order_number(): void
    {
        $currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );
        $order = Order::factory()->create([
            'order_number' => 12345,
            'currency_id' => $currency->id,
        ]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.orders.show', ['order_number' => 12345]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'order_number',
                'customer_id',
            ],
        ]);
        $response->assertJsonPath('data.order_number', '12345');
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
        $currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );
        $order = Order::factory()->create([
            'order_number' => 11111,
            'currency_id' => $currency->id,
        ]);

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

    // ========================================
    // Customer-scoped token tests
    // ========================================

    #[Test]
    public function it_allows_customer_scoped_token_to_view_own_order(): void
    {
        $currency = Currency::firstOrCreate(['code' => 'EUR'], ['name' => 'Euro']);
        $customer = Customer::factory()->create(['wp_id' => 111111]);
        Order::factory()->create([
            'order_number' => 22222,
            'currency_id' => $currency->id,
            'customer_id' => $customer->id,
        ]);

        $token = $this->createCustomerApiToken($customer);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.orders.show', ['order_number' => 22222]));

        $response->assertOk();
    }

    #[Test]
    public function it_forbids_customer_scoped_token_from_viewing_other_customers_order(): void
    {
        $currency = Currency::firstOrCreate(['code' => 'EUR'], ['name' => 'Euro']);
        $customer = Customer::factory()->create(['wp_id' => 111111]);
        $otherCustomer = Customer::factory()->create(['wp_id' => 222222]);
        Order::factory()->create([
            'order_number' => 33333,
            'currency_id' => $currency->id,
            'customer_id' => $otherCustomer->id,
        ]);

        $token = $this->createCustomerApiToken($customer);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.orders.show', ['order_number' => 33333]));

        $response->assertForbidden();
    }
}
