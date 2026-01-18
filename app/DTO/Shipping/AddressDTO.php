<?php

namespace App\DTO\Shipping;

readonly class AddressDTO
{
    public function __construct(
        public string $name,
        public string $company,
        public string $street1,
        public ?string $street2,
        public ?string $street3,
        public string $city,
        public ?string $state,
        public string $zip,
        public string $country,
        public string $email,
        public string $phone,
        public ?string $objectId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            company: $data['company'] ?? '',
            street1: $data['street1'] ?? $data['address_line1'] ?? '',
            street2: $data['street2'] ?? $data['address_line2'] ?? null,
            street3: $data['street3'] ?? $data['address_line3'] ?? null,
            city: $data['city'] ?? '',
            state: $data['state'] ?? null,
            zip: $data['zip'] ?? $data['postal_code'] ?? '',
            country: $data['country'] ?? '',
            email: $data['email'] ?? '',
            phone: $data['phone'] ?? '',
            objectId: $data['object_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'company' => $this->company,
            'street1' => $this->street1,
            'street2' => $this->street2,
            'street3' => $this->street3,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
            'object_id' => $this->objectId,
        ], fn ($value) => $value !== null);
    }

    public function toShippoArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'company' => $this->company,
            'street1' => $this->street1,
            'street2' => $this->street2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function isJapan(): bool
    {
        return strtoupper($this->country) === 'JP';
    }
}
