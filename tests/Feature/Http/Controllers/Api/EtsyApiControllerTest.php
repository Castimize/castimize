<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\ShopOwner;
use App\Services\Etsy\EtsyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
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
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createCustomerWithEtsyShop(): Customer
    {
        $customer = Customer::factory()->create();
        $shopOwner = ShopOwner::create([
            'customer_id' => $customer->id,
            'active' => true,
        ]);
        Shop::create([
            'shop_owner_id' => $shopOwner->id,
            'shop' => ShopOwnerShopsEnum::Etsy->value,
            'shop_oauth' => [
                'shop_id' => 12345678,
                'access_token' => 'test_token',
                'refresh_token' => 'test_refresh',
            ],
            'active' => true,
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
        ShopOwner::create([
            'customer_id' => $customer->id,
            'active' => true,
        ]);

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
}
