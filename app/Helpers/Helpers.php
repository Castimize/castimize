<?php

function currencyFormatter(float $amount, string $currency = 'USD', string $locale = 'nl_NL'): string
{
    return (new NumberFormatter($locale, NumberFormatter::CURRENCY))->formatCurrency($amount, $currency);
}
