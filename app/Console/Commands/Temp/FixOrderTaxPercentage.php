<?php

namespace App\Console\Commands\Temp;

use App\Models\Order;
use Illuminate\Console\Command;

class FixOrderTaxPercentage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-order-tax-percentage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fill tax percentage to order from woocommerce api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Order::with(['uploads'])
            ->withTrashed()
            ->whereNotNull('wp_id')
            ->whereNull('tax_percentage');
        $count = $query->count();
        $orders = $query->get();

        $progressBar = $this->output->createProgressBar($count);
        $this->info("Updating $count orders from Woocommerce");
        $progressBar->start();

        foreach ($orders as $order) {
            $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($order->wp_id);

            if ($wpOrder && count($wpOrder['tax_lines']) > 0) {
                $order->tax_percentage = $wpOrder['tax_lines'][0]->rate_percent;
                $order->save();
            }
            $progressBar->advance();
        }
        $progressBar->finish();

        return true;
    }
}
