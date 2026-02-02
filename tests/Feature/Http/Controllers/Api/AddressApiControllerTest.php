<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class AddressApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions(['viewPricing']);
    }

    // ========================================
    // validate() tests
    // ========================================

    #[Test]
    public function it_returns_invalid_when_country_is_empty(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.address.validate'), [
            'name' => 'John Doe',
            'address_1' => '123 Main Street',
            'city' => 'Amsterdam',
            'postal_code' => '1234AB',
            'country' => '',
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', false);
        $response->assertJsonPath('address_changed', 0);
    }

    #[Test]
    public function it_validates_address_with_all_fields(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.address.validate'), [
            'name' => 'John Doe',
            'company' => 'ACME Inc',
            'address_1' => '123 Main Street',
            'address_2' => 'Suite 100',
            'city' => 'Amsterdam',
            'state' => 'NH',
            'postal_code' => '1234AB',
            'country' => 'NL',
            'email' => 'john@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'valid',
            'address',
            'address_changed',
            'messages',
        ]);
    }

    #[Test]
    public function it_returns_401_when_not_authenticated(): void
    {
        $response = $this->postJson(route('api.api.address.validate'), [
            'name' => 'John Doe',
            'address_1' => '123 Main Street',
            'city' => 'Amsterdam',
            'postal_code' => '1234AB',
            'country' => 'NL',
        ]);

        $response->assertUnauthorized();
    }

    // ========================================
    // calculateShipping() tests
    // ========================================

    #[Test]
    public function it_returns_403_when_user_lacks_permission_for_calculate_shipping(): void
    {
        $this->setUpApiUserWithPermissions([]);
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.prices.calculate.shipping'), [
            'country' => 'NL',
            'uploads' => [],
        ]);

        $response->assertForbidden();
    }
}
