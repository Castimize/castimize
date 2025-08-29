<?php

namespace App\Console\Commands\Temp;

use App\Jobs\CreateRefundInvoicesFromOrder;
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
    public function handle(): int
    {
        $orderNumbers = [
            6213,
        ];
        $orders = Order::whereIn('order_number', $orderNumbers)
            ->orderByDesc('created_at')
            ->get();

        $totalOrders = $orders->count();
        $progressBar = $this->output->createProgressBar($totalOrders);

        $this->info("Creating refund invoices for $totalOrders");
        $progressBar->start();

        foreach ($orders as $order) {
            $this->info("Creating refund invoice for $order->order_number");
            CreateRefundInvoicesFromOrder::dispatch($order->wp_id);

            $progressBar->advance();
        }

        $progressBar->finish();

        return true;
    }
}
