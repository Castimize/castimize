<?php

namespace App\DTO\Order;

use App\DTO\Shops\Etsy\ListingDTO;
use App\Enums\Admin\CurrencyEnum;
use App\Enums\Woocommerce\WcOrderStatesEnum;
use app\Helpers\MonetaryAmount;
use App\Models\Country;
use App\Models\Shop;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\CurrencyService;
use App\Services\Admin\OrdersService;
use Carbon\Carbon;
use Etsy\Resources\Receipt;
use Illuminate\Support\Collection;
use TheIconic\NameParser\Parser;

class OrderDTO
{
    public function __construct(
        public int $customerId,
        public ?string $customerStripeId,
        public ?int $shopReceiptId,
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
        public bool $inCents,
        public ?MonetaryAmount $shippingFee,
        public ?MonetaryAmount $shippingFeeTax,
        public ?MonetaryAmount $discountFee,
        public ?MonetaryAmount $discountFeeTax,
        public ?MonetaryAmount $total,
        public ?MonetaryAmount $totalTax,
        public ?MonetaryAmount $totalRefund,
        public ?MonetaryAmount $totalRefundTax,
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
        public ?Collection $paymentFees,
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

        $isPaid = ! empty($wpOrder['date_paid']);
        $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $wpOrder['date_created_gmt']), 'GMT')?->setTimezone(env('APP_TIMEZONE'));
        $updatedAt = Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $wpOrder['date_modified_gmt']), 'GMT')?->setTimezone(env('APP_TIMEZONE'));

        $taxPercentage = null;
        if (count($wpOrder['tax_lines']) > 0) {
            $taxPercentage = $wpOrder['tax_lines'][0]->rate_percent;
        }

        return new self(
            customerId: $wpOrder['customer_id'],
            customerStripeId: null,
            shopReceiptId: null,
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
            inCents: false,
            shippingFee: MonetaryAmount::fromFloat((float) $wpOrder['shipping_total']),
            shippingFeeTax: MonetaryAmount::fromFloat((float) $wpOrder['shipping_tax']),
            discountFee: MonetaryAmount::fromFloat((float) $wpOrder['discount_total']),
            discountFeeTax: MonetaryAmount::fromFloat((float) $wpOrder['discount_tax']),
            total: MonetaryAmount::fromFloat((float) $wpOrder['total']),
            totalTax: MonetaryAmount::fromFloat((float) $wpOrder['total_tax']),
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
            isPaid: (bool) $wpOrder['date_paid'],
            paidAt: $wpOrder['date_paid'] ? Carbon::parse($wpOrder['date_paid']) : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            uploads: collect($wpOrder['line_items'])->map(fn ($lineItem) => UploadDTO::fromWpRequest($lineItem, $wpOrder['shipping']->country)),
//            paymentFees: collect($wpOrder['fee_lines'])->map(fn ($feeLine) => PaymentFeeDTO::fromWpRequest($wpOrder['payment_method'], $feeLine)),
            paymentFees: null,
        );
    }

    public static function fromEtsyReceipt(Shop $shop, Receipt $receipt, array $lines): OrderDTO
    {
        $parser = new Parser();

        $customer = $shop->shopOwner->customer;
        $billingAddress = $customer->addresses()->wherePivot('default_billing', 1)->first();

        $name = $parser->parse($receipt->name);

        $billingVatNumber = $customer->vat_number;
        $billingEmail = $receipt->buyer_email ?? $customer->email;
        $shippingEmail = $receipt->buyer_email ?? $receipt->seller_email;

        $isPaid = false;
        $createdAt = Carbon::createFromTimestamp($receipt->created_timestamp);
        $updatedAt = Carbon::createFromTimestamp($receipt->updated_timestamp);

        $taxPercentage = null;
        $vatExempt = 'yes';
        if ($billingVatNumber !== null && $billingAddress->country_id === 1) {
            $taxPercentage = 21;
            $vatExempt = 'no';
        }

        $shippingFee = (new CalculatePricesService())->calculateShippingFeeNew(
            countryIso: $receipt->country_iso,
            uploads: collect($lines)->map(fn ($line) => CalculateShippingFeeUploadDTO::fromEtsyLine($line)),
        )->calculated_total;

        if (
            app()->environment() === 'production' &&
            array_key_exists('shop_currency', $shop->shop_oauth) &&
            $shop->shop_oauth['shop_currency'] !== config('app.currency') &&
            in_array(CurrencyEnum::from($shop->shop_oauth['shop_currency']), CurrencyEnum::cases(), true)
        ) {
            /** @var CurrencyService $currencyService */
            $currencyService = app(CurrencyService::class);
            $shippingFee = $currencyService->convertCurrency(config('app.currency'), $shop->shop_oauth['shop_currency'], $shippingFee);
        }

        $shippingFeeTax = 0;
        $totalItems = 0;
        $totalItemsTax = 0;
        $uploads = collect($lines)->map(fn ($line) => UploadDTO::fromEtsyReceipt($shop, $receipt, $line, $taxPercentage));
        /** @var UploadDTO $upload */
        foreach ($uploads as $upload) {
            $totalItems += $upload->total->toFloat() * $upload->quantity;
        }

        if ($taxPercentage) {
            $shippingFeeTax = ($taxPercentage / 100) * $shippingFee;
            $totalItemsTax = ($taxPercentage / 100) * $totalItems;
        }

        $country = Country::where('alpha2', $receipt->country_iso)->first();
        $expectedDeliveryDate = (new OrdersService())->calculateExpectedDeliveryDate($uploads, $country);

        $metaData = [
            [
                'key' => '_shipping_email',
                'value' => $shippingEmail,
            ],
            [
                'key' => '_wc_order_attribution_utm_source',
                'value' => 'Etsy',
            ],
            [
                'key'=> '_wc_stripe_mode',
                'value'=> 'live',
            ],
            [
                'key' => 'is_vat_exempt',
                'value' => $vatExempt,
            ],
            [
                'key'=> 'wcpdf_order_locale',
                'value'=> 'en_US',
            ],
            [
                'key'=> '_expected_delivery_date',
                'value'=> $expectedDeliveryDate,
            ],
        ];

        if ($billingVatNumber) {
            $metaData[] = [
                'key' => '_billing_eu_vat_number',
                'value' => $billingVatNumber,
            ];
            $metaData[] = [
                'key' => 'billing_eu_vat_number_details',
                'value' => [
                    'vat_number' => [
                        'data' => $billingVatNumber ? substr($billingVatNumber, 0, 2) : null,
                        'label' => 'VAT Number',
                    ],
                    'country_code' => [
                        'data' => $billingVatNumber ? substr($billingVatNumber, 2) : null,
                        'label' => 'Country Code',
                    ],
                    'business_name' => [
                        'data' => $customer->company,
                        'label' => 'Business Name',
                    ],
                    'business_address' => [
                        'data' => $billingAddress->full_address_with_new_lines,
                        'label' => 'Business Address',
                    ],
                ],
            ];
        }

        $stripeData = $customer->stripe_data ?? [];

        $total = $totalItems + $shippingFee;
        $totalTax = $totalItemsTax + $shippingFeeTax;

        return new self(
            customerId: $customer->wp_id,
            customerStripeId: array_key_exists('stripe_id', $stripeData) ? $stripeData['stripe_id'] : null,
            shopReceiptId: (int) $receipt->receipt_id,
            source: 'etsy',
            wpId: null,
            orderNumber: $receipt->receipt_id,
            orderKey: $receipt->receipt_id,
            status: $receipt->status ?? WcOrderStatesEnum::Pending->value,
            firstName: $name->getFirstname(),
            lastName: $name->getMiddlename() !== '' ? $name->getMiddlename() . ' ' . $name->getLastName() : $name->getLastName(),
            email: $billingEmail,
            billingFirstName: $customer->first_name,
            billingLastName: $customer->last_name,
            billingCompany: $customer->company,
            billingPhoneNumber: $customer->phone,
            billingEmail: $customer->email,
            billingAddressLine1: $billingAddress->address_line1,
            billingAddressLine2: $billingAddress->address_line2,
            billingPostalCode: $billingAddress->postal_code,
            billingCity: $billingAddress->city->name,
            billingState: $billingAddress->state?->name,
            billingCountry: $billingAddress->country->alpha2,
            billingVatNumber: $billingVatNumber,
            shippingFirstName: $name->getFirstname(),
            shippingLastName: $name->getMiddlename() !== '' ? $name->getMiddlename() . ' ' . $name->getLastName() : $name->getLastName(),
            shippingCompany: null,
            shippingPhoneNumber: $customer->phone,
            shippingEmail: $shippingEmail,
            shippingAddressLine1: $receipt->first_line,
            shippingAddressLine2: $receipt->second_line,
            shippingPostalCode: $receipt->zip,
            shippingCity: ucfirst($receipt->city),
            shippingState: $receipt->state,
            shippingCountry: $receipt->country_iso,
            inCents: true,
            shippingFee: MonetaryAmount::fromFloat($shippingFee),
            shippingFeeTax: MonetaryAmount::fromFloat($shippingFeeTax),
            discountFee: null,
            discountFeeTax: null,
            total: MonetaryAmount::fromFloat($total),
            totalTax: MonetaryAmount::fromFloat($totalTax),
            totalRefund: null,
            totalRefundTax: null,
            taxPercentage: $taxPercentage,
            currencyCode: array_key_exists('shop_currency', $shop->shop_oauth) && in_array(CurrencyEnum::from($shop->shop_oauth['shop_currency']), CurrencyEnum::cases(), true) ? $shop->shop_oauth['shop_currency'] : 'USD',
            paymentMethod: $receipt->payment_method,
            paymentIssuer: $receipt->payment_method,
            paymentIntentId: null,
            customerIpAddress: null,
            customerUserAgent: 'Etsy API',
            metaData: $metaData,
            comments: 'Etsy receipt: ' . $receipt->receipt_id . '\n' . $receipt->message_from_buyer,
            promoCode: null,
            isPaid: $isPaid,
            paidAt: null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            uploads: $uploads,
            paymentFees: collect([
                PaymentFeeDTO::fromEtsyReceipt(
                    customer: $customer,
                    totalReceipt: $totalItems,
                    taxPercentage: $taxPercentage,
                ),
            ])
        );
    }
}
