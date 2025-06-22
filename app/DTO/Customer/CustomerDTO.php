<?php

namespace App\DTO\Customer;

use Illuminate\Http\Request;

readonly class CustomerDTO
{
    public function __construct(
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
    ) {
    }

    public static function fromApiRequest(Request $request): self
    {
        return new self(
            wpId: $request->wp_id ?? null,
            countryId: $request->country ?? null,
            currencyId: $request->currency ?? null,
            firstName: $request->firstName ?? null,
            lastName: $request->lastName ?? null,
            company: $request->company ?? null,
            email: $request->email ?? null,
            phone: $request->phone ?? null,
            vatNumber: $request->vatNumber ?? null,
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

    public static function fromWpCustomer(int $wpId): CustomerDTO
    {
        $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($wpId);
    }
}
