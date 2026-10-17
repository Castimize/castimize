<?php

declare(strict_types=1);

namespace Tests\Feature\Nova\Actions;

use App\Models\Customer;
use App\Models\User;
use App\Nova\Actions\EnsureCustomerApiUserAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EnsureCustomerApiUserActionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }

    #[Test]
    public function it_creates_and_links_an_api_user_for_a_customer_without_one(): void
    {
        $customer = Customer::factory()->create(['wp_id' => 111111, 'user_id' => null]);
        $action = new EnsureCustomerApiUserAction;

        $response = $action->handle(new ActionFields(collect(), collect()), new Collection([$customer]));

        $customer->refresh();
        $this->assertNotNull($customer->user_id);

        $user = User::find($customer->user_id);
        $this->assertSame(111111, $user->wp_id);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame(['path' => '/resources/users/'.$user->id, 'options' => []], $response['visit']);
    }

    #[Test]
    public function it_reuses_the_existing_linked_user_without_duplicating_it(): void
    {
        $existingUser = User::factory()->create(['wp_id' => 222222]);
        $existingUser->assignRole('customer');
        $customer = Customer::factory()->create(['wp_id' => 222222, 'user_id' => $existingUser->id]);
        $action = new EnsureCustomerApiUserAction;

        $action->handle(new ActionFields(collect(), collect()), new Collection([$customer]));

        $this->assertSame(1, User::where('wp_id', 222222)->count());
    }

    #[Test]
    public function it_refuses_to_run_on_more_than_one_customer_at_once(): void
    {
        $customers = Customer::factory()->count(2)->create();
        $action = new EnsureCustomerApiUserAction;

        $response = $action->handle(new ActionFields(collect(), collect()), new Collection($customers->all()));

        $this->assertNotNull($response['danger']);
    }
}
