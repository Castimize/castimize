<?php

namespace App\Enums\Admin;

use Laravel\Nova\Nova;

enum PaymentMethodsEnum: string
{
    case ACH_PAYMENT = 'ach';
    case ACH_DEBIT = 'us_bank_account';
    case AFFIRM = 'affirm';
    case AFTERPAY = 'afterpay';
    case ALIPAY = 'alipay';
    case AMAZON_PAY = 'amazonpay';
    case APPLE_PAY = 'apple_pay';
    case BANCONTACT = 'bancontact';
    case BECS = 'becs';
    case BLIK = 'blik';
    case BOLETO = 'boleto';
    case CARTES_BANCAIRES = 'cartes_bancaires';
    case CASH_ON_DELIVERY = 'cod';
    case CHEQUE = 'cheque';
    case CREDIT_CARD = 'card';
    case DIRECT_BANK_TRANSFER = 'bacs';
    case EPS = 'eps';
    case FPX = 'fpx';
    case GIROPAY = 'giropay';
    case GOOGLE_PAY = 'googlepay';
    case GRAB_PAY = 'grabpay';
    case IDEAL = 'ideal';
    case KLARNA = 'klarna';
    case KONBINI = 'konbini';
    case LINK = 'link';
    case MOBILE_PAY = 'mobilepay';
    case MULTIBANCO = 'multibanco';
    case OXXO = 'oxxo';
    case PAYNOW = 'paynow';
    case PAYPAL = 'ppcp';
    case PROMPT_PAY = 'promptpay';
    case PRZELEWY24 = 'p24';
    case REVOLUT = 'revolut';
    case SEPA = 'sepa';
    case SEPA_DEBIT = 'sepa_debit';
    case SOFORT = 'sofort';
    case SWISH = 'swish';
    case TWINT = 'twint';
    case WECHAT = 'wechat';
    case ZIP = 'zip';

    public static function options(): array
    {
        return [
            self::ACH_PAYMENT->value => (string) Nova::__('Ach'),
            self::AFFIRM->value => (string) Nova::__('Affirm'),
            self::AFTERPAY->value => (string) Nova::__('Afterpay'),
            self::ALIPAY->value => (string) Nova::__('Ali pay'),
            self::AMAZON_PAY->value => (string) Nova::__('Amazon pay'),
            self::APPLE_PAY->value => (string) Nova::__('Apple pay'),
            self::BOLETO->value => (string) Nova::__('Boleto'),
            self::BANCONTACT->value => (string) Nova::__('Bancontact'),
            self::BECS->value => (string) Nova::__('BECS'),
            self::BLIK->value => (string) Nova::__('Blik'),
            self::CARTES_BANCAIRES->value => (string) Nova::__('Cartes bancaires'),
            self::CASH_ON_DELIVERY->value => (string) Nova::__('Cash on delivery'),
            self::CHEQUE->value => (string) Nova::__('Cheque'),
            self::CREDIT_CARD->value => (string) Nova::__('Credit/Debit card'),
            self::DIRECT_BANK_TRANSFER->value => (string) Nova::__('Direct bank transfer'),
            self::EPS->value => (string) Nova::__('EPS'),
            self::FPX->value => (string) Nova::__('FPX'),
            self::GIROPAY->value => (string) Nova::__('Giro pay'),
            self::GOOGLE_PAY->value => (string) Nova::__('Google pay'),
            self::GRAB_PAY->value => (string) Nova::__('Grab pay'),
            self::IDEAL->value => (string) Nova::__('iDEAL'),
            self::KLARNA->value => (string) Nova::__('Klarna'),
            self::KONBINI->value => (string) Nova::__('Konbini'),
            self::LINK->value => (string) Nova::__('Link'),
            self::MOBILE_PAY->value => (string) Nova::__('Mobile pay'),
            self::MULTIBANCO->value => (string) Nova::__('Multibanco'),
            self::OXXO->value => (string) Nova::__('Oxxo'),
            self::PAYNOW->value => (string) Nova::__('Pay now'),
            self::PAYPAL->value => (string) Nova::__('Paypal'),
            self::PROMPT_PAY->value => (string) Nova::__('Prompt pay'),
            self::PRZELEWY24->value => (string) Nova::__('Przelewy24'),
            self::REVOLUT->value => (string) Nova::__('Revolut'),
            self::SEPA->value => (string) Nova::__('SEPA'),
            self::SEPA_DEBIT->value => (string) Nova::__('SEPA Debit'),
            self::SOFORT->value => (string) Nova::__('Sofort'),
            self::SWISH->value => (string) Nova::__('Swish'),
            self::TWINT->value => (string) Nova::__('Twint'),
            self::WECHAT->value => (string) Nova::__('WeChat'),
            self::ZIP->value => (string) Nova::__('Zip'),
        ];
    }

    public static function mandateOptions(): array
    {
        return [
            self::SEPA_DEBIT->value,
            self::ACH_DEBIT->value,
            self::CREDIT_CARD->value,
        ];
    }

    public static function getWoocommercePaymentMethod(?string $stripePaymentMethod): string
    {
        return match($stripePaymentMethod) {
            'sepa' => 'stripe_sepa',
            'us_bank_account' => 'stripe_ach',
            default => 'stripe_cc',
        };
    }
}
