<?php

namespace App\Services\Payment\Stripe;

use App\DTO\Order\OrderDTO;
use App\Models\Customer as CastimizeCustomer;
use Illuminate\Support\Str;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Collection;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Mandate;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\Stripe;

class StripeService
{
    private $stripeClient;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function getCustomers(?array $params = null): Collection
    {
        return Customer::all(params: $params);
    }

    public function createCustomer(CastimizeCustomer $customer): Customer
    {
        return Customer::create([
            'name' => $customer->name,
            'email' => $customer->email,
        ]);
    }

    /**
     * @return Balance
     * @throws ApiErrorException
     */
    public function getBalance(): Balance
    {
        return Balance::retrieve([]);
    }

    public function createCharge(int $amount, string $currency, string $customerId, string $sourceId, string $description = ''): Charge
    {
        return Charge::create([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customerId,
            'source' => $sourceId,
            'description' => $description,
        ]);
    }

    public function createSetupIntent(CastimizeCustomer $customer): SetupIntent
    {
        $data = [
            'customer' => $customer->stripe_data['stripe_id'],
            'usage' => 'off_session',
        ];

        if (! app()->environment('production')) {
            $data['payment_method_types'] = ['card', 'sepa_debit'];
        }
        return SetupIntent::create($data);
    }

    public function getPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    public function createPaymentIntent(OrderDTO $orderDTO, CastimizeCustomer $customer): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $orderDTO->total * 100,
            'currency' => strtolower($orderDTO->currencyCode),
            'customer' => $orderDTO->customerStripeId,
            'payment_method' => $customer->stripe_data['payment_method'],
            'mandate' => $customer->stripe_data['mandate_id'],
            'description' => 'Order ' . $orderDTO->wpId . ' from Castimize',
            'confirm' => true,
            'metadata' => [
                'shop_receipt_id' => $orderDTO->shopReceiptId,
                'source' => $orderDTO->customerUserAgent,
                'order_id' => $orderDTO->orderNumber,
            ],
        ]);
    }

    public function getPaymentMethod(string $paymentMethodId): ?PaymentMethod
    {
        if (Str::startsWith($paymentMethodId, 'pm_')) {
            return PaymentMethod::retrieve($paymentMethodId);
        }
        return null;
    }

    public function getPaymentMethods()
    {
        return PaymentIntent::create([
            'amount' => 1000,
            'currency' => 'eur',
            'automatic_payment_methods' => ['enabled' => true],
        ]);
    }

    public function detachPaymentMethod(PaymentMethod $paymentMethod): PaymentMethod
    {
        return $paymentMethod->detach();
    }

    public function getMandate(string $mandateId): Mandate
    {
        return Mandate::retrieve($mandateId);
    }
}
