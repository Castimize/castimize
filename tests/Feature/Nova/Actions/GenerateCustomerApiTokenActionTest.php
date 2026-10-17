<?php

declare(strict_types=1);

namespace Tests\Feature\Nova\Actions;

use App\Models\Customer;
use App\Nova\Actions\GenerateCustomerApiTokenAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateCustomerApiTokenActionTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_issues_a_token_usable_against_the_authorized_endpoints(): void
    {
        $customer = Customer::factory()->create(['wp_id' => 111111]);
        $action = new GenerateCustomerApiTokenAction;

        $response = $action->handle(new ActionFields(collect(), collect()), new Collection([$customer]));

        preg_match('/: ([^\s]+)$/', $response['message'], $matches);
        $token = $matches[1] ?? null;

        $this->assertNotNull($token);

        $apiResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.api.customers.show', ['customer' => $customer->id]));

        $apiResponse->assertOk();
    }

    #[Test]
    public function it_replaces_the_previous_admin_issued_token_on_regeneration(): void
    {
        $customer = Customer::factory()->create();
        $action = new GenerateCustomerApiTokenAction;

        $firstResponse = $action->handle(new ActionFields(collect(), collect()), new Collection([$customer]));
        preg_match('/: ([^\s]+)$/', $firstResponse['message'], $firstMatch);
        $firstToken = $firstMatch[1];

        $action->handle(new ActionFields(collect(), collect()), new Collection([$customer]));

        $this->assertCount(1, $customer->tokens()->where('name', GenerateCustomerApiTokenAction::TOKEN_NAME)->get());

        $apiResponse = $this->withHeader('Authorization', 'Bearer '.$firstToken)
            ->getJson(route('api.api.customers.show', ['customer' => $customer->id]));

        $apiResponse->assertUnauthorized();
    }

    #[Test]
    public function it_refuses_to_run_on_more_than_one_customer_at_once(): void
    {
        $customers = Customer::factory()->count(2)->create();
        $action = new GenerateCustomerApiTokenAction;

        $response = $action->handle(new ActionFields(collect(), collect()), new Collection($customers->all()));

        $this->assertNotNull($response['danger']);
    }
}
