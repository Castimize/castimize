<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\DTO\Order\OrderDTO;
use App\Enums\Admin\CurrencyEnum;
use App\Enums\Woocommerce\WcOrderStatesEnum;
use App\Helpers\MonetaryAmount;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait NeedsOrderDTO
{
    protected function createOrderDTO(array $overrides = [], ?Collection $uploads = null): OrderDTO
    {
        $orderNumber = $overrides['orderNumber'] ?? fake()->numberBetween(1000, 9999);

        return new OrderDTO(
            customerId: $overrides['customerId'] ?? 125,
            customerStripeId: $overrides['customerStripeId'] ?? null,
            shopReceiptId: $overrides['shopReceiptId'] ?? null,
            source: $overrides['source'] ?? 'wp',
            wpId: $overrides['wpId'] ?? $orderNumber,
            orderNumber: $orderNumber,
            orderKey: $overrides['orderKey'] ?? 'wc_order_'.fake()->uuid(),
            status: $overrides['status'] ?? WcOrderStatesEnum::Processing->value,
            firstName: $overrides['firstName'] ?? 'John',
            lastName: $overrides['lastName'] ?? 'Doe',
            email: $overrides['email'] ?? 'john@example.com',
            billingFirstName: $overrides['billingFirstName'] ?? $overrides['firstName'] ?? 'John',
            billingLastName: $overrides['billingLastName'] ?? $overrides['lastName'] ?? 'Doe',
            billingCompany: $overrides['billingCompany'] ?? null,
            billingPhoneNumber: $overrides['billingPhoneNumber'] ?? '+31612345678',
            billingEmail: $overrides['billingEmail'] ?? $overrides['email'] ?? 'john@example.com',
            billingAddressLine1: $overrides['billingAddressLine1'] ?? 'Test Street 1',
            billingAddressLine2: $overrides['billingAddressLine2'] ?? null,
            billingPostalCode: $overrides['billingPostalCode'] ?? '1234AB',
            billingCity: $overrides['billingCity'] ?? 'Amsterdam',
            billingState: $overrides['billingState'] ?? 'NH',
            billingCountry: $overrides['billingCountry'] ?? 'NL',
            billingVatNumber: $overrides['billingVatNumber'] ?? null,
            shippingFirstName: $overrides['shippingFirstName'] ?? $overrides['firstName'] ?? 'John',
            shippingLastName: $overrides['shippingLastName'] ?? $overrides['lastName'] ?? 'Doe',
            shippingCompany: $overrides['shippingCompany'] ?? null,
            shippingPhoneNumber: $overrides['shippingPhoneNumber'] ?? '+31612345678',
            shippingEmail: $overrides['shippingEmail'] ?? $overrides['email'] ?? 'john@example.com',
            shippingAddressLine1: $overrides['shippingAddressLine1'] ?? 'Test Street 1',
            shippingAddressLine2: $overrides['shippingAddressLine2'] ?? null,
            shippingPostalCode: $overrides['shippingPostalCode'] ?? '1234AB',
            shippingCity: $overrides['shippingCity'] ?? 'Amsterdam',
            shippingState: $overrides['shippingState'] ?? 'NH',
            shippingCountry: $overrides['shippingCountry'] ?? 'NL',
            inCents: $overrides['inCents'] ?? false,
            shippingFee: $overrides['shippingFee'] ?? MonetaryAmount::fromFloat(9.99),
            shippingFeeTax: $overrides['shippingFeeTax'] ?? MonetaryAmount::fromFloat(2.10),
            discountFee: $overrides['discountFee'] ?? null,
            discountFeeTax: $overrides['discountFeeTax'] ?? null,
            total: $overrides['total'] ?? MonetaryAmount::fromFloat(62.59),
            totalTax: $overrides['totalTax'] ?? MonetaryAmount::fromFloat(12.60),
            totalRefund: $overrides['totalRefund'] ?? null,
            totalRefundTax: $overrides['totalRefundTax'] ?? null,
            taxPercentage: $overrides['taxPercentage'] ?? 21.0,
            currencyCode: $overrides['currencyCode'] ?? CurrencyEnum::USD->value,
            paymentMethod: $overrides['paymentMethod'] ?? 'iDEAL',
            paymentIssuer: $overrides['paymentIssuer'] ?? 'ideal',
            transactionId: $overrides['transactionId'] ?? null,
            paymentIntentId: $overrides['paymentIntentId'] ?? 'pi_'.fake()->uuid(),
            customerIpAddress: $overrides['customerIpAddress'] ?? '192.168.1.1',
            customerUserAgent: $overrides['customerUserAgent'] ?? 'PHPUnit',
            metaData: $overrides['metaData'] ?? null,
            comments: $overrides['comments'] ?? null,
            promoCode: $overrides['promoCode'] ?? null,
            isPaid: $overrides['isPaid'] ?? true,
            paidAt: $overrides['paidAt'] ?? Carbon::now(),
            createdAt: $overrides['createdAt'] ?? Carbon::now(),
            updatedAt: $overrides['updatedAt'] ?? Carbon::now(),
            uploads: $uploads ?? collect(),
            paymentFees: $overrides['paymentFees'] ?? null,
        );
    }
}
