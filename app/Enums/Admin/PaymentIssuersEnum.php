<?php

declare(strict_types=1);

namespace App\Enums\Admin;

enum PaymentIssuersEnum: string
{
    case StripeOld = 'stripe';
    case StripeBancontact = 'stripe_bancontact';
    case StripeCreditCard = 'stripe_cc';
    case StripeIdeal = 'stripe_ideal';
    case StripeSepa = 'stripe_sepa';
    case StripeSofort = 'stripe_sofort';
    case Paypal = 'ppcp';
    case DirectBankTransfer = 'bacs';

    public static function getStripeMethods(): array
    {
        return [
            self::StripeOld->value,
            self::StripeBancontact->value,
            self::StripeCreditCard->value,
            self::StripeIdeal->value,
            self::StripeSepa->value,
            self::StripeSofort->value,
        ];
    }
}
