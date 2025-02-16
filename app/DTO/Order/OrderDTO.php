<?php

namespace App\DTO\Order;

use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class  OrderDTO
{
    public function __construct(
        public int $customerId,
        public string $source,
        public ?int $wpId,
        public int $orderNumber,
        public ?string $orderKey,
        public string $status,
        public ?string $firstName,
        public ?string $lastName,
        public string $email,
        public string $billingFirstName,
        public string $billingLastName,
        public ?string $billingCompany,
        public string $billingPhoneNumber,
        public string $billingEmail,
        public string $billingAddressLine1,
        public ?string $billingAddressLine2,
        public string $billingPostalCode,
        public string $billingCity,
        public ?string $billingState,
        public string $billingCountry,
        public ?string $billingVatNumber,
        public string $shippingFirstName,
        public string $shippingLastName,
        public ?string $shippingCompany,
        public ?string $shippingPhoneNumber,
        public ?string $shippingEmail,
        public string $shippingAddressLine1,
        public ?string $shippingAddressLine2,
        public string $shippingPostalCode,
        public string $shippingCity,
        public ?string $shippingState,
        public string $shippingCountry,
        public ?float $shippingFee,
        public ?float $shippingFeeTax,
        public ?float $discountFee,
        public ?float $discountFeeTax,
        public float $total,
        public ?float $totalTax,
        public ?float $totalRefund,
        public ?float $totalRefundTax,
        public ?float $taxPercentage,
        public string $currencyCode,
        public string $paymentMethod,
        public string $paymentIssuer,
        public ?string $paymentIntentId,
        public ?string $customerIpAddress,
        public ?string $customerUserAgent,
        public ?array $metaData,
        public ?string $comments,
        public ?string $promoCode,
        public bool $isPaid,
        public ?Carbon $paidAt,
        public ?Carbon $createdAt,
        public ?Carbon $updatedAt,
        public Collection $uploads,
    ) {
    }

    public static function fromApiRequest($request)
    {

    }

    public static function fromWpRequest($request): self
    {
        $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($request->id);

        $stripePaymentId = null;
        $billingVatNumber = null;
        $shippingEmail = null;
        foreach ($wpOrder['meta_data'] as $orderMetaData) {
            if ($orderMetaData->key === '_billing_eu_vat_number') {
                $billingVatNumber = $orderMetaData->value;
            }
            if ($orderMetaData->key === '_payment_intent_id') {
                $stripePaymentId = $orderMetaData->value;
            }
            if ($orderMetaData->key === '_shipping_email') {
                $shippingEmail = $orderMetaData->value;
            }
        }

        $isPaid = $wpOrder['date_paid'] !== null;
        $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $wpOrder['date_created_gmt']), 'GMT')?->setTimezone(env('APP_TIMEZONE'));
        $updatedAt = Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $wpOrder['date_modified_gmt']), 'GMT')?->setTimezone(env('APP_TIMEZONE'));

        $taxPercentage = null;
        if (count($wpOrder['tax_lines']) > 0) {
            $taxPercentage = $wpOrder['tax_lines'][0]->rate_percent;
        }

        return new self(
            customerId: $wpOrder['customer_id'],
            source: 'wp',
            wpId: $wpOrder['id'],
            orderNumber: $wpOrder['number'],
            orderKey: $wpOrder['order_key'],
            status: $wpOrder['status'],
            firstName: $wpOrder['billing']->first_name,
            lastName: $wpOrder['billing']->last_name,
            email: $wpOrder['billing']->email,
            billingFirstName: $wpOrder['billing']->first_name,
            billingLastName: $wpOrder['billing']->last_name,
            billingCompany: $wpOrder['billing']->company,
            billingPhoneNumber: $wpOrder['billing']->phone,
            billingEmail: $wpOrder['billing']->email,
            billingAddressLine1: $wpOrder['billing']->address_1,
            billingAddressLine2: $wpOrder['billing']->address_2,
            billingPostalCode: $wpOrder['billing']->postcode,
            billingCity: $wpOrder['billing']->city,
            billingState: $wpOrder['billing']->state,
            billingCountry: $wpOrder['billing']->country,
            billingVatNumber: $billingVatNumber,
            shippingFirstName: $wpOrder['shipping']->first_name,
            shippingLastName: $wpOrder['shipping']->last_name,
            shippingCompany: $wpOrder['shipping']->company,
            shippingPhoneNumber: $wpOrder['shipping']->phone ?? $wpOrder['billing']->phone,
            shippingEmail: $shippingEmail ?? $wpOrder['billing']->email,
            shippingAddressLine1: $wpOrder['shipping']->address_1,
            shippingAddressLine2: $wpOrder['shipping']->address_2,
            shippingPostalCode: $wpOrder['shipping']->postcode,
            shippingCity: $wpOrder['shipping']->city,
            shippingState: $wpOrder['shipping']->state,
            shippingCountry: $wpOrder['shipping']->country,
            shippingFee: $wpOrder['shipping_total'],
            shippingFeeTax: $wpOrder['shipping_tax'],
            discountFee: $wpOrder['discount_total'],
            discountFeeTax: $wpOrder['discount_tax'],
            total: $wpOrder['total'],
            totalTax: $wpOrder['total_tax'],
            totalRefund: null,
            totalRefundTax: null,
            taxPercentage: $taxPercentage,
            currencyCode: $wpOrder['currency'] ?? 'USD',
            paymentMethod: $wpOrder['payment_method_title'],
            paymentIssuer: $wpOrder['payment_method'],
            paymentIntentId: $stripePaymentId,
            customerIpAddress: $wpOrder['customer_ip_address'],
            customerUserAgent: $wpOrder['customer_user_agent'],
            metaData: $wpOrder['meta_data'],
            comments: $wpOrder['customer_note'],
            promoCode: null,
            isPaid: $isPaid !== null,
            paidAt: $wpOrder['date_paid'] ? Carbon::parse($wpOrder['date_paid']) : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            uploads: collect($wpOrder['line_items'])->map(fn ($lineItem) => UploadDTO::fromWpRequest($lineItem)),
        );
    }
}
