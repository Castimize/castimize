<?php

namespace App\Enums\Woocommerce;

enum WcOrderDocumentTypesEnum: string
{
    case Invoice = 'invoice';
    case CreditNote = 'credit-note';
}
