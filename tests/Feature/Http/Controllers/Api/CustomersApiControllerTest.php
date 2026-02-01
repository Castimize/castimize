<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomersApiControllerTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpUserWithPermissions();
    }

    private function setUpUserWithPermissions(): void
    {
        $this->role = Role::firstOrCreate(['name' => 'api-user', 'guard_name' => 'web']);

        $permissions = [
            'viewCustomer',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            $this->role->givePermissionTo($permission);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($this->role);
    }

    #[Test]
    public function it_returns_customer_by_id(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->user);

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

        Sanctum::actingAs($this->user);

        $response = $this->getJson(route('api.api.customers.show-customer-wp', ['wp_id' => 12345]));

        $response->assertOk();
        $response->assertJsonPath('data.wp_id', 12345);
        $response->assertJsonPath('data.first_name', $customer->first_name);
    }

    #[Test]
    public function it_returns_404_when_customer_wp_id_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson(route('api.api.customers.show-customer-wp', ['wp_id' => 99999]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_customer_with_order_count(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->user);

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
}
