<?php

namespace App\Enums\Woocommerce;

enum WcOrderFeeTaxStatesEnum: string
{
    case TAXABLE = 'taxable';
    case NONE = 'none';
}
