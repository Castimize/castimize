<?php

namespace App\Console\Commands\Temp;

use App\Models\Order;
use Illuminate\Console\Command;

class FixOrderBillingVatNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-order-billing-vat-number';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fill billing vat number to order from woocommerce api';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Order::withTrashed()
            ->whereNull('billing_vat_number')
            ->whereNotNull('wp_id');
        $count = $query->count();
        $orders = $query->get();

        $progressBar = $this->output->createProgressBar($count);
        $this->info("Updating $count orders from Woocommerce");
        $progressBar->start();

        foreach ($orders as $order) {
            $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($order->wp_id);

            if ($wpOrder) {
                $billingVatNumber = null;
                foreach ($wpOrder['meta_data'] as $orderMetaData) {
                    if ($orderMetaData->key === '_billing_eu_vat_number' && ! empty($orderMetaData->value)) {
                        $billingVatNumber = $orderMetaData->value;
                    }
                }
                $order->billing_vat_number = $billingVatNumber;
                $order->save();
            }
            $progressBar->advance();
        }
        $progressBar->finish();

        return true;
    }
}
