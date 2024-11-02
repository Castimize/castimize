<?php

namespace App\Console\Commands\Temp;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Console\Command;

class FixOrderLineItemWpId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-order-line-item-wp-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fill wp_id from order line item woocommerce api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Order::with(['uploads'])
            ->withTrashed()
            ->whereNotNull('wp_id');
        $count = $query->count();
        $orders = $query->get();

        $progressBar = $this->output->createProgressBar($count);
        $this->info("Updating $count orders from Woocommerce");
        $progressBar->start();

        foreach ($orders as $order) {
            $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($order->wp_id);
            if ($wpOrder) {
                foreach ($order->uploads as $upload) {
                    $lineItemWpId = null;
                    foreach ($wpOrder['line_items'] as $lineItem) {
                        foreach ($lineItem->meta_data as $metaData) {
                            $fileName = 'wp-content/uploads/p3d/' . $metaData->value;
                            if ($metaData->key === 'pa_p3d_model' && $upload->file_name === $fileName) {
                                $lineItemWpId = $lineItem->id;
                            }
                        }
                    }

                    if ($lineItemWpId) {
                        $upload->wp_id = $lineItemWpId;
                        $upload->save();
                    }
                }
            }
            $progressBar->advance();
        }
        $progressBar->finish();

        return true;
    }
}
