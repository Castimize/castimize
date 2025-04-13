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

    public static function fromApiRequest()
    {

    }

    public static function fromWpCustomer(int $wpId): CustomerDTO
    {
        $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($wpId);
    }
}
