<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class CustomersApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions(['viewCustomer']);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }

    /**
     * Create the customer-role User account a customer-scoped token belongs
     * to, matching how CustomersApiController::issueApiToken() and
     * UsersService::findOrCreateApiUser() link them, and return a plain-text
     * token for it.
     */
    private function createCustomerApiToken(Customer $customer): string
    {
        $user = User::factory()->create(['wp_id' => $customer->wp_id]);
        $user->assignRole('customer');
        $customer->update(['user_id' => $user->id]);

        return $user->createToken('test-token')->plainTextToken;
    }

    #[Test]
    public function it_returns_customer_by_id(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.show', ['customer' => $customer->id]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'wp_id',
                'email',
                'first_name',
                'last_name',
                'order_count',
                'date_created',
                'date_modified',
            ],
        ]);
        $response->assertJsonPath('data.wp_id', $customer->wp_id);
    }

    #[Test]
    public function it_allows_customer_scoped_token_to_view_own_record(): void
    {
        $customer = Customer::factory()->create(['wp_id' => 111111]);

        $token = $this->createCustomerApiToken($customer);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.customers.show', ['customer' => $customer->id]));

        $response->assertOk();
    }

    #[Test]
    public function it_forbids_customer_scoped_token_from_viewing_other_customers_record(): void
    {
        $customer = Customer::factory()->create(['wp_id' => 111111]);
        $otherCustomer = Customer::factory()->create(['wp_id' => 222222]);

        $token = $this->createCustomerApiToken($customer);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.customers.show', ['customer' => $otherCustomer->id]));

        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_403_when_user_lacks_permission(): void
    {
        $customer = Customer::factory()->create();

        $userWithoutPermission = User::factory()->create();
        Sanctum::actingAs($userWithoutPermission);

        $response = $this->getJson(route('api.api.customers.show', ['customer' => $customer->id]));

        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_401_when_not_authenticated(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson(route('api.api.customers.show', ['customer' => $customer->id]));

        $response->assertUnauthorized();
    }

    #[Test]
    public function it_returns_customer_by_wp_id(): void
    {
        $customer = Customer::factory()->create(['wp_id' => 12345]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.show-customer-wp', ['wp_id' => 12345]));

        $response->assertOk();
        $response->assertJsonPath('data.wp_id', 12345);
        $response->assertJsonPath('data.first_name', $customer->first_name);
    }

    #[Test]
    public function it_returns_404_when_customer_wp_id_not_found(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.show-customer-wp', ['wp_id' => 99999]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_customer_with_order_count(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.customers.show-customer-wp', ['wp_id' => $customer->wp_id]));

        $response->assertOk();
        $response->assertJsonPath('data.order_count', 0);
    }

    #[Test]
    public function it_deletes_customer_by_wp_id(): void
    {
        $customer = Customer::factory()->create(['wp_id' => 54321]);

        $payload = ['id' => 54321];
        $payloadJson = json_encode($payload);

        $response = $this->call(
            'DELETE',
            route('api.api.customers.delete-customer.wp'),
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'Content-Type' => 'application/json',
                'x-wc-webhook-signature' => base64_encode(
                    hash_hmac('sha256', $payloadJson, config('services.woocommerce.key'), true)
                ),
            ]),
            $payloadJson
        );

        $response->assertNoContent();
        $this->assertSoftDeleted('customers', ['wp_id' => 54321]);
    }

    #[Test]
    public function it_returns_404_when_deleting_nonexistent_customer(): void
    {
        $payload = ['id' => 99999];
        $payloadJson = json_encode($payload);

        $response = $this->call(
            'DELETE',
            route('api.api.customers.delete-customer.wp'),
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'Content-Type' => 'application/json',
                'x-wc-webhook-signature' => base64_encode(
                    hash_hmac('sha256', $payloadJson, config('services.woocommerce.key'), true)
                ),
            ]),
            $payloadJson
        );

        $response->assertNotFound();
    }

    // ========================================
    // issueApiToken() tests
    // ========================================

    #[Test]
    public function it_issues_a_customer_scoped_api_token(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.customers.issue-token', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertOk();
        $response->assertJsonStructure(['token']);
    }

    #[Test]
    public function it_issues_tokens_for_multiple_customers_with_unique_usernames(): void
    {
        $customerA = Customer::factory()->create(['wp_id' => 910001, 'first_name' => 'Jane', 'last_name' => 'Doe']);
        $customerB = Customer::factory()->create(['wp_id' => 910002, 'first_name' => 'John', 'last_name' => 'Roe']);

        Sanctum::actingAs($this->apiUser);

        foreach ([$customerA, $customerB] as $customer) {
            $this->postJson(route('api.api.customers.issue-token', [
                'customerId' => $customer->wp_id,
            ]))->assertOk();
        }

        $usernames = User::whereIn('wp_id', [910001, 910002])->pluck('username')->all();

        $this->assertCount(2, $usernames);
        $this->assertNotContains('', $usernames);
        $this->assertSame('jane-doe-910001', User::where('wp_id', 910001)->value('username'));
    }

    #[Test]
    public function issued_token_can_only_access_its_own_customer_shop_owner_data(): void
    {
        $ownCustomer = Customer::factory()->create(['wp_id' => 111111]);
        $otherCustomer = Customer::factory()->create(['wp_id' => 222222]);

        // Use a real bearer token for the staff call (rather than Sanctum::actingAs,
        // which would keep overriding auth for the later requests below, regardless
        // of the Authorization header we send).
        $staffToken = $this->apiUser->createToken('staff')->plainTextToken;

        $issueResponse = $this->withHeader('Authorization', 'Bearer '.$staffToken)
            ->postJson(route('api.api.customers.issue-token', [
                'customerId' => $ownCustomer->wp_id,
            ]));
        $token = $issueResponse->json('token');

        // Laravel's test client resolves and caches the authenticated user on
        // the guard instance for the lifetime of the test; without forgetting
        // it here, the requests below would keep authenticating as the staff
        // user from the call above regardless of the Authorization header.
        Auth::forgetGuards();

        $ownResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.customers.shop-owners.show', [
                'customerId' => $ownCustomer->wp_id,
            ]));
        $ownResponse->assertOk();

        Auth::forgetGuards();

        $otherResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.customers.shop-owners.show', [
                'customerId' => $otherCustomer->wp_id,
            ]));
        $otherResponse->assertForbidden();
    }

    #[Test]
    public function it_returns_404_when_issuing_token_for_nonexistent_customer(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.customers.issue-token', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_403_when_issuing_token_without_permission(): void
    {
        $customer = Customer::factory()->create();
        $userWithoutPermission = User::factory()->create();
        Sanctum::actingAs($userWithoutPermission);

        $response = $this->postJson(route('api.api.customers.issue-token', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertForbidden();
    }

    #[Test]
    public function it_forbids_a_customer_scoped_token_from_issuing_a_token_for_another_customer(): void
    {
        // In production, the 'customer' role is granted the 'viewCustomer'
        // permission (so customers can view their own record via Nova/API),
        // which would otherwise let a customer-scoped token pass the
        // Gate::denies('viewCustomer') check here and mint a fresh token
        // impersonating any other customer. Replicate that grant explicitly,
        // since this test's role isn't seeded with it by default.
        Permission::firstOrCreate(['name' => 'viewCustomer', 'guard_name' => 'web']);
        Role::findByName('customer')->givePermissionTo('viewCustomer');

        $ownCustomer = Customer::factory()->create(['wp_id' => 333333]);
        $otherCustomer = Customer::factory()->create(['wp_id' => 444444]);
        $token = $this->createCustomerApiToken($ownCustomer);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(route('api.api.customers.issue-token', [
                'customerId' => $otherCustomer->wp_id,
            ]));

        $response->assertForbidden();
    }
}
