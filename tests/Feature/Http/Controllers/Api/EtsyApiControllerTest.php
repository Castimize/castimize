<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\ShopOwner;
use App\Models\User;
use App\Services\Etsy\EtsyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class EtsyApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions([]);
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createCustomerWithEtsyShop(): Customer
    {
        $customer = Customer::factory()->create();
        $shopOwner = ShopOwner::factory()->create(['customer_id' => $customer->id]);
        Shop::factory()->etsy()->create([
            'shop_owner_id' => $shopOwner->id,
            'shop_oauth' => [
                'shop_id' => 12345678,
                'access_token' => 'test_token',
                'refresh_token' => 'test_refresh',
            ],
        ]);
        $customer->load('shopOwner.shops');

        return $customer;
    }

    // ========================================
    // getShopAuthorizationUrl() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_auth_url(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-shop-authorization-url', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_404_when_customer_has_no_etsy_shop(): void
    {
        $customer = Customer::factory()->create();
        ShopOwner::factory()->create(['customer_id' => $customer->id]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-shop-authorization-url', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_authorization_url_for_etsy_shop(): void
    {
        $customer = $this->createCustomerWithEtsyShop();

        // Mock the EtsyService
        $this->mock(EtsyService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAuthorizationUrl')
                ->once()
                ->andReturn('https://www.etsy.com/oauth/connect?response_type=code&client_id=test');
        });

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-shop-authorization-url', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertOk();
        $response->assertJsonStructure(['url']);
    }

    // ========================================
    // getTaxonomy() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_taxonomy(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-taxonomy', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_404_when_customer_has_no_shop_owner(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-taxonomy', [
            'customerId' => $customer->id,
        ]));

        $response->assertNotFound();
    }

    // ========================================
    // getShop() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_shop(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-shop', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    // ========================================
    // getListings() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_listings(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-listings', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    // ========================================
    // getShopReceipts() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_receipts(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-shop-receipts', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    // ========================================
    // getShippingProfile() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_shipping_profile(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.etsy.get-shipping-profile', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    // ========================================
    // Authentication tests
    // ========================================

    #[Test]
    public function it_returns_401_when_not_authenticated(): void
    {
        $response = $this->getJson(route('api.api.etsy.get-shop', [
            'customerId' => 12345,
        ]));

        $response->assertUnauthorized();
    }

    // ========================================
    // Customer-scoped token tests
    //
    // Note: getEtsyShopOrFail() resolves {customerId} via Customer::find(),
    // i.e. the primary key - not wp_id like the rest of the API - so these
    // tests use $customer->id to match the real routing, same as the
    // existing it_returns_404_when_customer_has_no_shop_owner test above.
    // ========================================

    #[Test]
    public function it_allows_customer_scoped_token_to_view_own_etsy_listings(): void
    {
        $customer = $this->createCustomerWithEtsyShop();

        $this->mock(EtsyService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getListings')->once()->andReturn(new \Etsy\Collection('Listing'));
        });

        $token = $this->createCustomerApiToken($customer);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.etsy.get-listings', [
                'customerId' => $customer->id,
            ]));

        $response->assertOk();
    }

    #[Test]
    public function it_forbids_customer_scoped_token_from_viewing_other_customers_etsy_listings(): void
    {
        $customer = Customer::factory()->create();
        $otherCustomer = $this->createCustomerWithEtsyShop();

        // Authorization should abort before EtsyService is ever used, but
        // the controller's constructor injection resolves it regardless of
        // whether the method body reaches it - mock it so that resolution
        // doesn't try to build a real Etsy client without credentials.
        $this->mock(EtsyService::class);

        $token = $this->createCustomerApiToken($customer);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.etsy.get-listings', [
                'customerId' => $otherCustomer->id,
            ]));

        $response->assertForbidden();
    }
}
