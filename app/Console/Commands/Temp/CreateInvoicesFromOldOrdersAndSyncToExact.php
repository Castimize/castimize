<?php

namespace App\Console\Commands\Temp;

use App\Jobs\CreateInvoicesFromOrder;
use App\Jobs\SetOrderPaid;
use App\Jobs\SyncCustomerToExact;
use App\Jobs\SyncInvoiceToExact;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\Payment\Stripe\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CreateInvoicesFromOldOrdersAndSyncToExact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:create-invoices-from-old-orders-and-sync-to-exact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to create invoices from old-orders and sync to Exact';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::with(['uploads'])
            ->doesntHave('invoiceLines')
            ->orderByDesc('created_at')
            ->get();

//        $order = Order::find(145);
//        $order = $orders->where('id', 127)->first();
//
//        $invoice = Invoice::with(['customer', 'lines'])->find(4);
//
//        Bus::chain([
//            new SyncCustomerToExact($invoice->customer->wp_id),
//            new SyncInvoiceToExact($invoice, $invoice->customer->wp_id),
//        ])->onQueue('default')->dispatch();

        foreach ($orders as $order) {
            CreateInvoicesFromOrder::dispatch($order->wp_id);

            //dd($order);
        }
    }
}
