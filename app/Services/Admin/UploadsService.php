<?php

namespace App\Services\Admin;

use App\Enums\Admin\OrderStatusesEnum;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\Upload;

class UploadsService
{
    public function __construct(
        private OrderQueuesService $orderQueuesService,
    ) {
    }

    /**
     * @param Upload $upload
     * @return void
     */
    public function setUploadToOrderQueue(Upload $upload): void
    {
        $orderQueuesService = new OrderQueuesService();
        $manufacturer = Manufacturer::with(['costs'])->orderBy('id')->first();

        $orderQueues = $orderQueuesService->storeFromUpload($upload, [$manufacturer]);

        // Create a order queue status in-queue for all order queues
        foreach ($orderQueues as $orderQueue) {
            $orderQueuesService->setStatus($orderQueue);
        }
    }

    public function handleStripeRefund(Order $order): void
    {
        $wcOrderRefunds = \Codexshaper\WooCommerce\Facades\Order::refunds($order->wp_id);
        foreach ($wcOrderRefunds as $refund) {
            $lineItems = $refund->line_items;
            foreach ($lineItems as $lineItem) {
                $upload = null;
                foreach ($lineItem->meta_data as $metaData) {
                    if ($metaData->key === '_refunded_item_id') {
                        $upload = Upload::where('wp_id', $metaData->value)->first();
                    }
                }
                if ($upload) {
                    $upload->total_refund = abs($lineItem->total);
                    $upload->total_refund_tax = abs($lineItem->total_tax);
                    $upload->save();

                    $this->orderQueuesService->setStatus($upload->orderQueue, OrderStatusesEnum::Canceled->value);
                }
            }
        }
    }
}
