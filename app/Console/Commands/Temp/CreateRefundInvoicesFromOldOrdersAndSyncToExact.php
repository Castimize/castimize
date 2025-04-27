<?php

namespace App\Console\Commands\Temp;

use App\Jobs\CreateInvoicesFromOrder;
use App\Models\Order;
use Illuminate\Console\Command;

class CreateRefundInvoicesFromOldOrdersAndSyncToExact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:create-refund-invoices-from-old-orders-and-sync-to-exact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to create refund invoices from old-orders and sync to Exact';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderNumbers = [
            6167,
            6209,
            6246,
            6270,
        ];
        $orders = Order::whereIn('order_number', $orderNumbers)
            ->orderByDesc('created_at')
            ->get();

        foreach ($orders as $order) {
            CreateInvoicesFromOrder::dispatch($order->wp_id);
        }
    }
}
