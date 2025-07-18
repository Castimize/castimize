<?php

namespace App\DTO\Order;

use App\Enums\Admin\PaymentFeeTypesEnum;
use App\Enums\Admin\PaymentMethodsEnum;
use App\Enums\Woocommerce\WcOrderFeeTaxStatesEnum;
use app\Helpers\MonetaryAmount;
use App\Models\Customer;
use App\Models\PaymentFee;
use App\Services\Payment\Stripe\StripeService;

class  PaymentFeeDTO
{
    public function __construct(
        public string $paymentMethod,
        public string $name,
        public string $taxClass,
        public WcOrderFeeTaxStatesEnum $taxStatus,
        public MonetaryAmount $total,
        public ?MonetaryAmount $totalTax,
        public array $taxes = [],
        public array $metaData = [],
    ) {
    }

    public static function fromWpRequest(string $paymentMethod, $feeLine): self
    {
        return new self(
            paymentMethod: $paymentMethod,
            name: $feeLine->name,
            taxClass: $feeLine->taxClass,
            taxStatus: $feeLine->taxStatus,
            total: MonetaryAmount::fromFloat((float) $feeLine->total),
            totalTax: MonetaryAmount::fromFloat((float) $feeLine->totalTax),
            taxes: $feeLine,
            metaData: $feeLine->metaData,
        );
    }

    public static function fromEtsyReceipt(Customer $customer, float $totalReceipt, ?int $taxPercentage = null): PaymentFeeDTO
    {
        $total = 0.00;
        $totalTax = 0.00;

        $paymentMethod = (new StripeService())->getPaymentMethod($customer->stripe_data['payment_method']);
        $paymentMethodEnum = PaymentMethodsEnum::from($paymentMethod->type);
        $paymentMethodName = PaymentMethodsEnum::options()[$paymentMethod->type] . ' usage & Handling fee';

        $paymentFee = PaymentFee::where('payment_method', $paymentMethodEnum->value)->first();
        if ($paymentFee) {
            if (PaymentFeeTypesEnum::from($paymentFee->type) === PaymentFeeTypesEnum::FIXED) {
                $total = $paymentFee->fee;
            } else {
                $total = $totalReceipt * ($paymentFee->fee / 100);
            }

            if ($taxPercentage) {
                $totalTax = ($taxPercentage / 100) * $total;
            }
        }

        return new self(
            paymentMethod: $paymentMethodEnum->value,
            name: $paymentMethodName,
            taxClass: '',
            taxStatus: WcOrderFeeTaxStatesEnum::TAXABLE,
            total: MonetaryAmount::fromFloat($total),
            totalTax: MonetaryAmount::fromFloat($totalTax),
            taxes: [],
            metaData: [
                [
                    'key' => '_last_added_fee',
                    'value' => $paymentMethodName,
                ]
            ],
        );
    }
}
