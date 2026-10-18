<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressApiControllerTest extends TestCase
{
    use DatabaseTransactions;

    // ========================================
    // validate() tests
    //
    // Public endpoint: no customer-private data, and needed during guest
    // checkout before a customer necessarily has any account/token.
    // ========================================

    #[Test]
    public function it_returns_invalid_when_country_is_empty(): void
    {
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
    public function it_works_without_any_authentication(): void
    {
        $response = $this->postJson(route('api.api.address.validate'), [
            'name' => 'John Doe',
            'address_1' => '123 Main Street',
            'city' => 'Amsterdam',
            'postal_code' => '1234AB',
            'country' => 'NL',
        ]);

        $response->assertOk();
    }
}
