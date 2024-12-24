<?php

namespace App\Services\Admin;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\Model;
use App\Models\Order;
use App\Models\User;
use App\Services\Woocommerce\WoocommerceApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoicesService
{
    public function __construct()
    {
    }

    public function storeInvoiceFromWpOrder(Customer $customer, Order $order)
    {
        $systemUser = User::find(1);
        $wpOrder = $order->wpOrder;

        $invoiceNumber = null;
        $invoiceDate = now();
        $billingVatNumber = null;
        foreach ($wpOrder['meta_data'] as $metaData) {
            if ($metaData->key === '_wcpdf_invoice_number') {
                $invoiceNumber = $metaData->value;
            }
            if ($metaData->key === '_wcpdf_invoice_date_formatted') {
                $invoiceDate = Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $metaData->value), 'GMT')?->setTimezone(env('APP_TIMEZONE'));
            }
            if ($metaData->key === '_billing_eu_vat_number') {
                $billingVatNumber = $metaData->value;
            }
        }

        $invoice = Invoice::create([
            'wp_id' => $wpOrder['id'],
            'customer_id' => $customer->id,
            'currency_id' => $order->currency_id,
            'country_id' => $order->country_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $wpOrder['paid_at'],
            'total' => $wpOrder['total'],
            'total_tax' => $wpOrder['total_tax'],
            'currency_code' => $order->currency?->code ?? 'USD',
            'description' => __('Order #:orderNumber', ['orderNumber' => $wpOrder['id']]),
            'email' => $wpOrder['billing']->email,
            'contact_person' => $wpOrder['billing']->first_name . ' ' . $wpOrder['billing']->last_name,
            'address_line1' => $wpOrder['billing']->address1,
            'address_line2' => $wpOrder['billing']->address2,
            'postal_code' => $wpOrder['billing']->postcode,
            'city' => $wpOrder['billing']->city,
            'country' => $wpOrder['billing']->country,
            'tax_percentage' => $order->tax_percentage,
            'vat_number' => $billingVatNumber,
            'sent' => true,
            'sent_at' => $wpOrder['date_paid'],
            'paid' => $order->paid,
            'paid_at' => $order->paid_at,
            'meta_data' => $wpOrder['meta_data'],
            'created_by' => $systemUser->id,
            'created_at' => $invoiceDate,
            'updated_by' => $systemUser->id,
            'updated_at' => $invoiceDate,
        ]);

        foreach ($order->uploads as $upload) {
            $invoice->lines()->create([
                'order_id' => $order->id,
                'upload_id' => $upload->id,
                'customer_id' => $customer->id,
                'currency_id' => $upload->currency_id,
                'upload_name' => $upload->name,
                'material_name' => $upload->material_name,
                'quantity' => $upload->quantity,
                'total' => $upload->total,
                'total_tax' => $upload->total_tax,
                'currency_code' => $upload->currency_code,
                'meta_data' => $upload->meta_data,
            ]);
        }
    }
}
