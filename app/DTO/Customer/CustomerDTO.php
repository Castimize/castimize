<?php

declare(strict_types=1);

namespace App\DTO\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class CustomerDTO extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $wpId,
        public ?int $countryId,
        public ?int $currencyId,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $company,
        public ?string $email,
        public ?string $phone,
        public ?string $vatNumber,
        public ?string $billingContactName,
        public ?string $billingCompany,
        public ?string $billingPhone,
        public ?string $billingEmail,
        public ?string $billingAddressLine1,
        public ?string $billingAddressLine2,
        public ?string $billingPostalCode,
        public ?string $billingCity,
        public ?string $billingState,
        public ?string $billingCountry,
        public ?string $shippingContactName,
        public ?string $shippingCompany,
        public ?string $shippingPhone,
        public ?string $shippingEmail,
        public ?string $shippingAddressLine1,
        public ?string $shippingAddressLine2,
        public ?string $shippingPostalCode,
        public ?string $shippingCity,
        public ?string $shippingState,
        public ?string $shippingCountry,
    ) {}

    public static function fromApiRequest(Customer $customer, Request $request): self
    {
        return new self(
            id: $customer->id,
            wpId: isset($request->wp_id) ? (int) $request->wp_id : ($customer->wp_id ?? null),
            countryId: isset($request->country) ? (int) $request->country : null,
            currencyId: isset($request->currency) ? (int) $request->currency : null,
            firstName: $request->firstName ?? null,
            lastName: $request->lastName ?? null,
            company: $request->company ?? null,
            email: $request->email ?? null,
            phone: $request->phone ?? null,
            vatNumber: $request->vatNumber ?? $customer->vat_number ?? null,
            billingContactName: $request->billingContactName ?? null,
            billingCompany: $request->billingCompany ?? null,
            billingPhone: $request->billingPhone ?? null,
            billingEmail: $request->billingEmail ?? null,
            billingAddressLine1: $request->billingAddressLine1 ?? null,
            billingAddressLine2: $request->billingAddressLine2 ?? null,
            billingPostalCode: $request->billingPostalCode ?? null,
            billingCity: $request->billingCity ?? null,
            billingState: $request->billingState ?? null,
            billingCountry: $request->billingCountry ?? null,
            shippingContactName: $request->shippingContactName ?? null,
            shippingCompany: $request->shippingCompany ?? null,
            shippingPhone: $request->shippingPhone ?? null,
            shippingEmail: $request->shippingEmail ?? null,
            shippingAddressLine1: $request->shippingAddressLine1 ?? null,
            shippingAddressLine2: $request->shippingAddressLine2 ?? null,
            shippingPostalCode: $request->shippingPostalCode ?? null,
            shippingCity: $request->shippingCity ?? null,
            shippingState: $request->shippingState ?? null,
            shippingCountry: $request->shippingCountry ?? null,
        );
    }
}
