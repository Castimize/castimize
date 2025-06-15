<?php

namespace App\Services\Payment\Stripe;

use Stripe\Balance;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeService
{
    private $stripeClient;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function getCustomers(?array $params = null)
    {
        return Customer::all(params: $params);
    }

    /**
     * @return Balance
     * @throws ApiErrorException
     */
    public function getBalance(): Balance
    {
        return Balance::retrieve([]);
    }

    public function createCharge(int $amount, string $currency, string $customerId, string $sourceId, string $description = '')
    {
        return Charge::create([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customerId,
            'source' => $sourceId,
            'description' => $description,
        ]);
    }

    public function getPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }
}
