<?php

/**
 * @param float  $amount
 * @param string $currency
 * @param string $locale
 *
 * @return string
 */
function currencyFormatter(float $amount, string $currency = 'USD', string $locale = 'nl_NL'): string
{
    return (new NumberFormatter($locale, NumberFormatter::CURRENCY))->formatCurrency($amount, $currency);
}
