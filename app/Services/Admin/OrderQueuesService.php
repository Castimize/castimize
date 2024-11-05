<?php

namespace App\Services\Admin;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\OrderQueueStatus;
use App\Models\OrderStatus;
use App\Models\Upload;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class OrderQueuesService
{
    /**
     * Store a order queue from upload
     * @param Upload $upload
     * @param array $manufacturers
     * @return array<OrderQueue>
     */
    public function storeFromUpload(Upload $upload, array $manufacturers): array
    {
        $orderQueues = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerCost = $manufacturer->costs->where('active', true)->where('material_id', $upload->material_id)->first();
            $shippingFee = $upload->order->country->logisticsZone->shippingFee;
            if ($manufacturerCost) {
                $orderQueues[] = $upload->orderQueue()->create([
                    'order_id' => $upload->order_id,
                    'shipping_fee_id' => $shippingFee?->id,
                    'manufacturer_id' => $manufacturer->id,
                    'manufacturer_cost_id' => $manufacturerCost->id,
                    'manufacturer_costs' => (new CalculatePricesService())->calculateCostsOfModel(
                        $manufacturerCost,
                        $upload->model_volume_cc,
                        $upload->model_surface_area_cm2,
                        $upload->quantity
                    ),
                    'due_date' => $upload->order->due_date,
                    'final_arrival_date' => Carbon::parse($upload->order->created_at)->addBusinessDays($upload->customer_lead_time),
                ]);
            }
        }

        return $orderQueues;
    }

    /**
     * @param OrderQueue $orderQueue
     * @param string $orderStatusSlug
     * @return OrderQueueStatus
     */
    public function setStatus(OrderQueue $orderQueue, string $orderStatusSlug = 'in-queue'): OrderQueueStatus
    {
        // Create a order queue status in-queue
        $orderStatus = OrderStatus::where('slug', $orderStatusSlug)->first();
        return $orderQueue->orderQueueStatuses()->create([
            'order_status_id' => $orderStatus->id,
            'status' => $orderStatus->status,
            'slug' => $orderStatus->slug,
            'target_date' => $orderQueue->calculateTargetDate($orderStatusSlug),
        ]);
    }

    public function getRefundLineItem(OrderQueue $orderQueue, float $total, $wpOrderLinItems): array
    {
        $refundTax = $this->getRefundTax($orderQueue, $wpOrderLinItems);
        $refundLineItem = [
            'id' => (string)$orderQueue->upload->wp_id,
            'refund_total' => $total,
        ];
        if (count($refundTax) > 0) {
            //$refundLineItem['refund_tax'] = $refundTax;
        }
        return $refundLineItem;
    }

    /**
     * @param mixed $orderQueue
     * @param $lineItems
     * @return array|array[]
     */
    public function getRefundTax(mixed $orderQueue, $lineItems): array
    {
        $refundTax = [];
        if ($orderQueue->upload->total_tax > 0.00) {
            foreach ($lineItems as $lineItem) {
                if ($lineItem->id === $orderQueue->upload->wp_id) {
                    $taxId = $lineItem->taxes[0]?->id;
                    if ($taxId) {
                        $refundTax = [
                            [
                                'id' => (string)$taxId,
                                'amount' => (float)$orderQueue->upload->total_tax,
                            ]
                        ];
                    }
                }
            }
        }
        return $refundTax;
    }
}
