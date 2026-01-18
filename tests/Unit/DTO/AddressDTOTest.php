<?php

namespace Tests\Unit\DTO;

use App\DTO\Shipping\AddressDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddressDTOTest extends TestCase
{
    #[Test]
    public function from_array_maps_alias_fields(): void
    {
        $dto = AddressDTO::fromArray([
            'name' => 'John Doe',
            'company' => 'Acme',
            'address_line1' => '1-2-3 Shibuya',
            'address_line2' => 'Building 4F',
            'address_line3' => 'Unit 12',
            'city' => 'Tokyo',
            'state' => 'Tokyo',
            'postal_code' => '150-0002',
            'country' => 'JP',
            'email' => 'john@example.com',
            'phone' => '+81 3-1234-5678',
            'object_id' => 'shippo_123',
        ]);

        $this->assertSame('John Doe', $dto->name);
        $this->assertSame('Acme', $dto->company);
        $this->assertSame('1-2-3 Shibuya', $dto->street1);
        $this->assertSame('Building 4F', $dto->street2);
        $this->assertSame('Unit 12', $dto->street3);
        $this->assertSame('Tokyo', $dto->city);
        $this->assertSame('Tokyo', $dto->state);
        $this->assertSame('150-0002', $dto->zip);
        $this->assertSame('JP', $dto->country);
        $this->assertSame('john@example.com', $dto->email);
        $this->assertSame('+81 3-1234-5678', $dto->phone);
        $this->assertSame('shippo_123', $dto->objectId);
    }

    #[Test]
    public function to_array_filters_null_values_but_keeps_empty_strings(): void
    {
        $dto = new AddressDTO(
            name: 'Jane',
            company: '',
            street1: 'Street 1',
            street2: null,
            street3: null,
            city: 'Amsterdam',
            state: null,
            zip: '1011AA',
            country: 'NL',
            email: '',
            phone: '',
            objectId: null,
        );

        $array = $dto->toArray();

        $this->assertArrayNotHasKey('street2', $array);
        $this->assertArrayNotHasKey('street3', $array);
        $this->assertArrayNotHasKey('state', $array);
        $this->assertArrayNotHasKey('object_id', $array);

        $this->assertSame('', $array['company']);
        $this->assertSame('', $array['email']);
        $this->assertSame('', $array['phone']);
    }

    #[Test]
    public function to_shippo_array_filters_null_and_empty_strings(): void
    {
        $dto = new AddressDTO(
            name: 'Jane',
            company: '',
            street1: 'Street 1',
            street2: '',
            street3: null,
            city: 'Amsterdam',
            state: null,
            zip: '1011AA',
            country: 'NL',
            email: '',
            phone: '',
            objectId: 'ignored_here',
        );

        $array = $dto->toShippoArray();

        $this->assertSame('Jane', $array['name']);
        $this->assertSame('Street 1', $array['street1']);
        $this->assertSame('Amsterdam', $array['city']);
        $this->assertSame('1011AA', $array['zip']);
        $this->assertSame('NL', $array['country']);

        $this->assertArrayNotHasKey('company', $array);
        $this->assertArrayNotHasKey('street2', $array);
        $this->assertArrayNotHasKey('state', $array);
        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('phone', $array);
        $this->assertArrayNotHasKey('object_id', $array);
    }

    #[Test]
    public function is_japan_is_case_insensitive(): void
    {
        $jp = new AddressDTO(
            name: 'A',
            company: 'B',
            street1: 'C',
            street2: null,
            street3: null,
            city: 'D',
            state: null,
            zip: 'E',
            country: 'jp',
            email: 'a@b.com',
            phone: '1',
        );

        $nl = new AddressDTO(
            name: 'A',
            company: 'B',
            street1: 'C',
            street2: null,
            street3: null,
            city: 'D',
            state: null,
            zip: 'E',
            country: 'NL',
            email: 'a@b.com',
            phone: '1',
        );

        $this->assertTrue($jp->isJapan());
        $this->assertFalse($nl->isJapan());
    }
}
