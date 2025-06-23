<?php

namespace App\Services\Admin;

use App\Enums\Woocommerce\WcOrderDocumentTypesEnum;
use App\Jobs\SyncCustomerToExact;
use App\Jobs\SyncInvoiceToExact;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

class InvoicesService
{
    public function __construct()
    {
    }

    public function storeInvoiceFromWpOrder(Customer $customer, Order $order, bool $debit = true)
    {
        $systemUser = User::find(1);
        $wpOrder = $order->wpOrder;

        $billingVatNumber = null;
        foreach ($wpOrder['meta_data'] as $metaData) {
            if ($metaData->key === '_billing_eu_vat_number' && !empty($metaData->value)) {
                $billingVatNumber = $metaData->value;
            }
        }

        if ($debit) {
            $invoiceDocument = WcOrderDocumentTypesEnum::Invoice->value;
            $invoiceNumber = $wpOrder['documents']->$invoiceDocument->number;
            $invoiceDate = Carbon::createFromTimestamp($wpOrder['documents']->$invoiceDocument->date_timestamp);
            $total = $wpOrder['total'];
            $totalTax = $wpOrder['total_tax'];
        } else {
            $creditNoteDocument = WcOrderDocumentTypesEnum::CreditNote->value;
            $invoiceNumber = $wpOrder['documents']->$creditNoteDocument->number;
            $invoiceDate = Carbon::createFromTimestamp($wpOrder['documents']->$creditNoteDocument->date_timestamp);
            $total = 0.00;
            foreach ($wpOrder['refunds'] as $refund) {
                $total += abs($refund->total);
            }
            $totalTax = ($order->tax_percentage / 100) * $total;
        }

        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();
        if ($invoice) {
            return $invoice;
        }

        $invoice = Invoice::create([
            'wp_id' => $wpOrder['id'],
            'customer_id' => $customer->id,
            'currency_id' => $order->currency_id,
            'country_id' => $order->country_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'debit' => $debit,
            'total' => $total,
            'total_tax' => $totalTax,
            'currency_code' => $order->currency_code,
            'description' => __('Order #:orderNumber', ['orderNumber' => $wpOrder['id']]),
            'email' => $wpOrder['billing']->email,
            'contact_person' => $wpOrder['billing']->first_name . ' ' . $wpOrder['billing']->last_name,
            'address_line1' => $wpOrder['billing']->address_1,
            'address_line2' => $wpOrder['billing']->address_2,
            'postal_code' => $wpOrder['billing']->postcode,
            'city' => $wpOrder['billing']->city,
            'country' => $wpOrder['billing']->country,
            'tax_percentage' => $order->tax_percentage,
            'vat_number' => $billingVatNumber,
            'sent' => true,
            'sent_at' => $wpOrder['date_paid'],
            'paid' => $order->is_paid,
            'paid_at' => $order->paid_at,
            'meta_data' => $wpOrder['meta_data'],
            'created_by' => $systemUser->id,
            'created_at' => $invoiceDate,
            'updated_by' => $systemUser->id,
            'updated_at' => $invoiceDate,
        ]);

        if ($debit) {
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
        } else {
            foreach ($wpOrder['refunds'] as $refund) {
                $refundTotal = 0.00;
                $refundTotal += abs($refund->total);
                $refundTotalTax = ($order->tax_percentage / 100) * $refundTotal;

                $invoice->lines()->create([
                    'order_id' => $order->id,
                    'upload_id' => null,
                    'customer_id' => $customer->id,
                    'currency_id' => $order->currency_id,
                    'upload_name' => '-',
                    'material_name' => '-',
                    'quantity' => 1,
                    'total' => $refundTotal,
                    'total_tax' => $refundTotalTax,
                    'currency_code' => $order->currency_code,
                    'meta_data' => null,
                ]);
            }
        }

        Bus::chain([
            new SyncCustomerToExact($customer->wp_id),
            new SyncInvoiceToExact($invoice, $customer->wp_id),
        ])
            ->onQueue('exact')
            ->dispatch();

        return $invoice;
    }
}
