<?php

namespace App\Services\Payment\Stripe;

use App\DTO\Order\OrderDTO;
use App\Enums\Admin\PaymentMethodsEnum;
use App\Models\Customer as CastimizeCustomer;
use Exception;
use Illuminate\Support\Str;
use Stripe\Balance;
use Stripe\BalanceTransaction;
use Stripe\Charge;
use Stripe\Collection;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
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
     * @throws ApiErrorException
     */
    public function getBalance(): Balance
    {
        return Balance::retrieve([]);
    }

    public function getCharge(string $chargeId): Charge
    {
        return Charge::retrieve($chargeId);
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

    public function getBalanceTransaction(string $balanceTransactionId): BalanceTransaction
    {
        return BalanceTransaction::retrieve($balanceTransactionId);
    }

    public function getSetupIntent(string $setupIntentId): SetupIntent
    {
        return SetupIntent::retrieve($setupIntentId);
    }

    public function createSetupIntent(CastimizeCustomer $customer): SetupIntent
    {
        $data = [
            'customer' => $customer->stripe_data['stripe_id'],
            'payment_method_types' => PaymentMethodsEnum::mandateOptions(),
            'usage' => 'off_session',
            'metadata' => [
                'customer_id' => $customer->id,
                'wp_id' => $customer->wp_id,
            ],
        ];

        return SetupIntent::create($data);
    }

    public function getPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    public function createPaymentIntent(OrderDTO $orderDTO, CastimizeCustomer $customer): PaymentIntent
    {
        $paymentMethod = $this->getPaymentMethod($customer->stripe_data['payment_method']);
        $total = $orderDTO->total;
        foreach ($orderDTO->paymentFees as $paymentFee) {
            $total = $total->add($paymentFee->total);
        }
        return PaymentIntent::create([
            'amount' => $total->getValue(),
            'currency' => strtolower($orderDTO->currencyCode),
            'customer' => $orderDTO->customerStripeId,
            'payment_method' => $customer->stripe_data['payment_method'],
            'mandate' => $customer->stripe_data['mandate_id'],
            'description' => 'Order ' . $orderDTO->wpId . ' from Castimize',
            'confirm' => true,
            'off_session' => true,
            'return_url' => env('APP_SITE_URL'),
            'payment_method_types' => [
                $paymentMethod->type,
            ],
            'metadata' => [
                'shop_receipt_id' => $orderDTO->shopReceiptId,
                'source' => $orderDTO->customerUserAgent,
                'order_id' => $orderDTO->orderNumber,
            ],
        ]);
    }

    public function createTestPaymentIntent(CastimizeCustomer $customer, string $paymentMethodId): array
    {
        try {
            $stripeData = $customer->stripe_data ?? [];

            $intent = PaymentIntent::create([
                'amount' => 100, // €1, in cents
                'currency' => 'eur',
                'customer' => $stripeData['stripe_id'],
                'payment_method' => $paymentMethodId,
                'off_session' => true,
                'confirm' => true,
                'capture_method' => 'manual', // zodat we niet echt afschrijven
            ]);

            if ($intent->status === 'requires_capture') {
                // Betaling gelukt → annuleren om geld niet af te schrijven
                $intent->cancel();

                return [
                    'success' => true,
                    'status'  => 'usable',
                    'message' => 'Kaart is bruikbaar voor off-session betalingen.',
                ];
            }

            return [
                'success' => false,
                'status'  => $intent->status,
                'message' => 'Onverwachte status ontvangen.',
            ];
        } catch (CardException $e) {
            $error = $e->getError();

            if ($error?->code === 'authentication_required') {
                return [
                    'success' => false,
                    'status'  => 'requires_authentication',
                    'message' => 'Kaart vereist SCA, niet bruikbaar off-session.',
                ];
            }

            return [
                'success' => false,
                'status'  => $error?->code ?? 'card_error',
                'message' => $error?->message,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentMethod(string $paymentMethodId): ?PaymentMethod
    {
        if (Str::startsWith($paymentMethodId, 'pm_')) {
            return PaymentMethod::retrieve($paymentMethodId);
        }
        return null;
    }

    public function getPaymentMethods(): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => 1000,
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
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
