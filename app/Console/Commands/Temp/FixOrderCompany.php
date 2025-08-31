<?php

namespace App\Console\Commands\Temp;

use App\Models\Order;
use Illuminate\Console\Command;

class FixOrderCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-order-company';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fill billing company and shipping company to order from woocommerce api';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Order::withTrashed()
            ->whereNotNull('wp_id');
        $count = $query->count();
        $orders = $query->get();

        $progressBar = $this->output->createProgressBar($count);
        $this->info("Updating $count orders from Woocommerce");
        $progressBar->start();

        foreach ($orders as $order) {
            $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($order->wp_id);

            $order->billing_company = $wpOrder['billing']->company;
            $order->shipping_company = $wpOrder['shipping']->company;
            $order->save();
            $progressBar->advance();
        }
        $progressBar->finish();

        return true;
    }
}
