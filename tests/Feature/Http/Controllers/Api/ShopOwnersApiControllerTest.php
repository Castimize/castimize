<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\ShopOwner;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class ShopOwnersApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions(['viewCustomer']);
    }

    private function createCustomerWithShopOwner(): Customer
    {
        $customer = Customer::factory()->create();
        $shopOwner = ShopOwner::create([
            'customer_id' => $customer->id,
            'active' => true,
        ]);
        $customer->setRelation('shopOwner', $shopOwner);

        return $customer;
    }

    private function createShopForOwner(ShopOwner $shopOwner, string $shopType = 'etsy'): Shop
    {
        return Shop::create([
            'shop_owner_id' => $shopOwner->id,
            'shop' => $shopType,
            'shop_oauth' => ['shop_id' => 12345],
            'active' => true,
        ]);
    }

    // ========================================
    // show() tests
    // ========================================

    #[Test]
    public function it_returns_shop_owner_for_customer(): void
    {
        $customer = $this->createCustomerWithShopOwner();

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.shop-owners.show', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'active',
            ],
        ]);
    }

    #[Test]
    public function it_returns_404_when_customer_not_found_for_show(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.shop-owners.show', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_403_when_user_lacks_permission(): void
    {
        $customer = $this->createCustomerWithShopOwner();

        $userWithoutPermission = User::factory()->create();
        Sanctum::actingAs($userWithoutPermission);

        $response = $this->getJson(route('api.api.customers.shop-owners.show', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertForbidden();
    }

    // ========================================
    // showShop() tests
    // ========================================

    #[Test]
    public function it_returns_shop_for_customer(): void
    {
        $customer = $this->createCustomerWithShopOwner();
        $this->createShopForOwner($customer->shopOwner, 'etsy');

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.shop-owners.show-shop', [
            'customerId' => $customer->wp_id,
            'shop' => 'etsy',
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'shop',
                'active',
            ],
        ]);
        $response->assertJsonPath('data.shop', 'etsy');
    }

    #[Test]
    public function it_returns_404_when_shop_not_found(): void
    {
        $customer = $this->createCustomerWithShopOwner();

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.shop-owners.show-shop', [
            'customerId' => $customer->wp_id,
            'shop' => 'nonexistent',
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_404_when_customer_has_no_shop_owner(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.shop-owners.show-shop', [
            'customerId' => $customer->wp_id,
            'shop' => 'etsy',
        ]));

        $response->assertNotFound();
    }

    // ========================================
    // store() tests
    // ========================================

    #[Test]
    public function it_creates_shop_owner_for_customer(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.customers.shop-owners.store', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('shop_owners', [
            'customer_id' => $customer->id,
        ]);
    }

    #[Test]
    public function it_returns_400_when_shop_owner_already_exists(): void
    {
        $customer = $this->createCustomerWithShopOwner();

        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.customers.shop-owners.store', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertStatus(400);
    }

    // ========================================
    // update() tests
    // ========================================

    #[Test]
    public function it_returns_400_when_updating_nonexistent_shop_owner(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $response = $this->putJson(route('api.api.customers.shop-owners.update', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertStatus(400);
    }

    // ========================================
    // updateActive() tests
    // ========================================

    #[Test]
    public function it_updates_shop_owner_active_state(): void
    {
        $customer = $this->createCustomerWithShopOwner();

        Sanctum::actingAs($this->apiUser);

        $response = $this->putJson(route('api.api.customers.shop-owners.update-active', [
            'customerId' => $customer->wp_id,
        ]), [
            'active' => '0',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('shop_owners', [
            'customer_id' => $customer->id,
            'active' => false,
        ]);
    }

    // ========================================
    // Authentication tests
    // ========================================

    #[Test]
    public function it_returns_401_when_not_authenticated(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson(route('api.api.customers.shop-owners.show', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertUnauthorized();
    }
}
