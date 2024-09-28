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
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($amount, $currency);
}
