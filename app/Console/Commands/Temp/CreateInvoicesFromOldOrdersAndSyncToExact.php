<?php

namespace App\Console\Commands\Temp;

use App\Jobs\CreateInvoicesFromOrder;
use App\Models\Order;
use Illuminate\Console\Command;

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

        foreach ($orders as $order) {
            CreateInvoicesFromOrder::dispatch($order->wp_id);
        }
    }
}
