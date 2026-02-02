<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\ShopOwner;
use App\Services\Admin\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class PaymentsApiControllerTest extends TestCase
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

    private function createCustomerWithShopOwner(): Customer
    {
        $customer = Customer::factory()->create([
            'stripe_data' => [
                'customer_id' => 'cus_test123',
                'mandate_id' => 'mandate_test123',
            ],
        ]);
        $shopOwner = ShopOwner::create([
            'customer_id' => $customer->id,
            'active' => true,
        ]);
        Shop::create([
            'shop_owner_id' => $shopOwner->id,
            'shop' => 'etsy',
            'shop_oauth' => ['shop_id' => 12345],
            'active' => true,
        ]);
        $customer->load('shopOwner.shops');

        return $customer;
    }

    // ========================================
    // createSetupIntent() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_setup_intent(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.payments.create-setup-intent', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_creates_setup_intent_for_customer(): void
    {
        $customer = Customer::factory()->create();

        // Mock the PaymentService
        $this->mock(PaymentService::class, function (MockInterface $mock) {
            $setupIntent = new \stdClass;
            $setupIntent->client_secret = 'seti_test_secret_123';
            $mock->shouldReceive('createStripeSetupIntent')->once()->andReturn($setupIntent);
        });

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.payments.create-setup-intent', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertOk();
        $response->assertJsonStructure(['client_secret']);
        $response->assertJsonPath('client_secret', 'seti_test_secret_123');
    }

    // ========================================
    // attachPaymentMethod() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_attach_payment(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.customers.payments.attach-payment-method', [
            'customerId' => 99999,
        ]), [
            'payment_method' => 'pm_test123',
        ]);

        $response->assertNotFound();
    }

    #[Test]
    public function it_attaches_payment_method_to_customer(): void
    {
        $customer = $this->createCustomerWithShopOwner();

        // Mock the PaymentService
        $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('attachStripePaymentMethod')->once();
        });

        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.customers.payments.attach-payment-method', [
            'customerId' => $customer->wp_id,
        ]), [
            'payment_method' => 'pm_test123',
        ]);

        $response->assertNoContent();
    }

    // ========================================
    // cancelMandate() tests
    // ========================================

    #[Test]
    public function it_returns_404_when_customer_not_found_for_cancel_mandate(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.customers.payments.cancel-mandate', [
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
        $response = $this->getJson(route('api.api.customers.payments.create-setup-intent', [
            'customerId' => 12345,
        ]));

        $response->assertUnauthorized();
    }
}
