<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Customer;

use App\DTO\Customer\CustomerDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new CustomerDTO(
            id: 1,
            wpId: 100,
            countryId: 1,
            currencyId: 1,
            firstName: 'John',
            lastName: 'Doe',
            company: 'Acme Inc',
            email: 'john@example.com',
            phone: '+31612345678',
            vatNumber: 'NL123456789B01',
            billingContactName: 'John Doe',
            billingCompany: 'Acme Inc',
            billingPhone: '+31612345678',
            billingEmail: 'billing@acme.com',
            billingAddressLine1: '123 Main St',
            billingAddressLine2: 'Apt 4',
            billingPostalCode: '1011 AB',
            billingCity: 'Amsterdam',
            billingState: 'NH',
            billingCountry: 'NL',
            shippingContactName: 'John Doe',
            shippingCompany: 'Acme Inc',
            shippingPhone: '+31612345678',
            shippingEmail: 'shipping@acme.com',
            shippingAddressLine1: '456 Oak Ave',
            shippingAddressLine2: null,
            shippingPostalCode: '2011 BB',
            shippingCity: 'Rotterdam',
            shippingState: 'ZH',
            shippingCountry: 'NL',
        );

        $this->assertEquals(1, $dto->id);
        $this->assertEquals(100, $dto->wpId);
        $this->assertEquals(1, $dto->countryId);
        $this->assertEquals(1, $dto->currencyId);
        $this->assertEquals('John', $dto->firstName);
        $this->assertEquals('Doe', $dto->lastName);
        $this->assertEquals('Acme Inc', $dto->company);
        $this->assertEquals('john@example.com', $dto->email);
        $this->assertEquals('+31612345678', $dto->phone);
        $this->assertEquals('NL123456789B01', $dto->vatNumber);
        $this->assertEquals('John Doe', $dto->billingContactName);
        $this->assertEquals('Acme Inc', $dto->billingCompany);
        $this->assertEquals('+31612345678', $dto->billingPhone);
        $this->assertEquals('billing@acme.com', $dto->billingEmail);
        $this->assertEquals('123 Main St', $dto->billingAddressLine1);
        $this->assertEquals('Apt 4', $dto->billingAddressLine2);
        $this->assertEquals('1011 AB', $dto->billingPostalCode);
        $this->assertEquals('Amsterdam', $dto->billingCity);
        $this->assertEquals('NH', $dto->billingState);
        $this->assertEquals('NL', $dto->billingCountry);
        $this->assertEquals('John Doe', $dto->shippingContactName);
        $this->assertEquals('456 Oak Ave', $dto->shippingAddressLine1);
        $this->assertNull($dto->shippingAddressLine2);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_parameters(): void
    {
        $dto = new CustomerDTO(
            id: null,
            wpId: null,
            countryId: null,
            currencyId: null,
            firstName: null,
            lastName: null,
            company: null,
            email: null,
            phone: null,
            vatNumber: null,
            billingContactName: null,
            billingCompany: null,
            billingPhone: null,
            billingEmail: null,
            billingAddressLine1: null,
            billingAddressLine2: null,
            billingPostalCode: null,
            billingCity: null,
            billingState: null,
            billingCountry: null,
            shippingContactName: null,
            shippingCompany: null,
            shippingPhone: null,
            shippingEmail: null,
            shippingAddressLine1: null,
            shippingAddressLine2: null,
            shippingPostalCode: null,
            shippingCity: null,
            shippingState: null,
            shippingCountry: null,
        );

        $this->assertNull($dto->id);
        $this->assertNull($dto->wpId);
        $this->assertNull($dto->firstName);
        $this->assertNull($dto->email);
        $this->assertNull($dto->vatNumber);
        $this->assertNull($dto->billingAddressLine1);
        $this->assertNull($dto->shippingAddressLine1);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new CustomerDTO(
            id: 5,
            wpId: 500,
            countryId: 2,
            currencyId: 2,
            firstName: 'Jane',
            lastName: 'Smith',
            company: 'Smith Corp',
            email: 'jane@smith.com',
            phone: '+1234567890',
            vatNumber: null,
            billingContactName: 'Jane Smith',
            billingCompany: 'Smith Corp',
            billingPhone: '+1234567890',
            billingEmail: 'jane@smith.com',
            billingAddressLine1: '100 Broadway',
            billingAddressLine2: null,
            billingPostalCode: '10001',
            billingCity: 'New York',
            billingState: 'NY',
            billingCountry: 'US',
            shippingContactName: 'Jane Smith',
            shippingCompany: null,
            shippingPhone: '+1234567890',
            shippingEmail: 'jane@smith.com',
            shippingAddressLine1: '100 Broadway',
            shippingAddressLine2: null,
            shippingPostalCode: '10001',
            shippingCity: 'New York',
            shippingState: 'NY',
            shippingCountry: 'US',
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(5, $array['id']);
        $this->assertEquals(500, $array['wpId']);
        $this->assertEquals('Jane', $array['firstName']);
        $this->assertEquals('Smith', $array['lastName']);
        $this->assertEquals('jane@smith.com', $array['email']);
        $this->assertEquals('100 Broadway', $array['billingAddressLine1']);
        $this->assertEquals('New York', $array['billingCity']);
        $this->assertEquals('US', $array['billingCountry']);
    }

    #[Test]
    public function it_can_be_created_from_array(): void
    {
        $dto = CustomerDTO::from([
            'id' => 10,
            'wpId' => 1000,
            'countryId' => 3,
            'currencyId' => 1,
            'firstName' => 'Bob',
            'lastName' => 'Johnson',
            'company' => null,
            'email' => 'bob@johnson.com',
            'phone' => '+44123456789',
            'vatNumber' => 'GB123456789',
            'billingContactName' => 'Bob Johnson',
            'billingCompany' => null,
            'billingPhone' => '+44123456789',
            'billingEmail' => 'bob@johnson.com',
            'billingAddressLine1' => '10 Downing St',
            'billingAddressLine2' => null,
            'billingPostalCode' => 'SW1A 2AA',
            'billingCity' => 'London',
            'billingState' => null,
            'billingCountry' => 'GB',
            'shippingContactName' => 'Bob Johnson',
            'shippingCompany' => null,
            'shippingPhone' => '+44123456789',
            'shippingEmail' => 'bob@johnson.com',
            'shippingAddressLine1' => '10 Downing St',
            'shippingAddressLine2' => null,
            'shippingPostalCode' => 'SW1A 2AA',
            'shippingCity' => 'London',
            'shippingState' => null,
            'shippingCountry' => 'GB',
        ]);

        $this->assertEquals(10, $dto->id);
        $this->assertEquals(1000, $dto->wpId);
        $this->assertEquals('Bob', $dto->firstName);
        $this->assertEquals('Johnson', $dto->lastName);
        $this->assertEquals('GB123456789', $dto->vatNumber);
        $this->assertEquals('London', $dto->billingCity);
        $this->assertEquals('GB', $dto->billingCountry);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new CustomerDTO(
            id: 20,
            wpId: 2000,
            countryId: 1,
            currencyId: 1,
            firstName: 'Alice',
            lastName: 'Brown',
            company: 'Brown Ltd',
            email: 'alice@brown.com',
            phone: '+31687654321',
            vatNumber: 'NL987654321B01',
            billingContactName: 'Alice Brown',
            billingCompany: 'Brown Ltd',
            billingPhone: '+31687654321',
            billingEmail: 'alice@brown.com',
            billingAddressLine1: '50 Canal St',
            billingAddressLine2: null,
            billingPostalCode: '1012 AB',
            billingCity: 'Amsterdam',
            billingState: null,
            billingCountry: 'NL',
            shippingContactName: 'Alice Brown',
            shippingCompany: null,
            shippingPhone: '+31687654321',
            shippingEmail: 'alice@brown.com',
            shippingAddressLine1: '50 Canal St',
            shippingAddressLine2: null,
            shippingPostalCode: '1012 AB',
            shippingCity: 'Amsterdam',
            shippingState: null,
            shippingCountry: 'NL',
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(20, $decoded['id']);
        $this->assertEquals('Alice', $decoded['firstName']);
        $this->assertEquals('alice@brown.com', $decoded['email']);
    }

    #[Test]
    public function it_handles_different_billing_and_shipping_addresses(): void
    {
        $dto = new CustomerDTO(
            id: 30,
            wpId: 3000,
            countryId: 1,
            currencyId: 1,
            firstName: 'Charlie',
            lastName: 'Davis',
            company: null,
            email: 'charlie@davis.com',
            phone: '+31612121212',
            vatNumber: null,
            billingContactName: 'Charlie Davis',
            billingCompany: null,
            billingPhone: '+31612121212',
            billingEmail: 'charlie@davis.com',
            billingAddressLine1: 'Billing Address 1',
            billingAddressLine2: null,
            billingPostalCode: '1000 AA',
            billingCity: 'Amsterdam',
            billingState: null,
            billingCountry: 'NL',
            shippingContactName: 'Charlie Davis',
            shippingCompany: 'Work Address',
            shippingPhone: '+31634343434',
            shippingEmail: 'charlie.work@davis.com',
            shippingAddressLine1: 'Shipping Address 1',
            shippingAddressLine2: 'Floor 5',
            shippingPostalCode: '2000 BB',
            shippingCity: 'Rotterdam',
            shippingState: null,
            shippingCountry: 'NL',
        );

        $this->assertEquals('Billing Address 1', $dto->billingAddressLine1);
        $this->assertEquals('Amsterdam', $dto->billingCity);
        $this->assertEquals('Shipping Address 1', $dto->shippingAddressLine1);
        $this->assertEquals('Rotterdam', $dto->shippingCity);
        $this->assertEquals('Floor 5', $dto->shippingAddressLine2);
        $this->assertNotEquals($dto->billingCity, $dto->shippingCity);
    }
}
