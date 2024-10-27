<?php

namespace App\Http\Controllers\Webhooks\Shipping;

use App\Http\Controllers\Webhooks\WebhookController;
use App\Models\CustomerShipment;
use App\Models\ManufacturerShipment;
use App\Services\Admin\OrderQueuesService;
use App\Services\Woocommerce\WoocommerceApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class ShippoWebhookController extends WebhookController
{
    /**
     * Handle a Shippo webhook call.
     *
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        try {
            $shippoRequest = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $event = $shippoRequest['event'];
        } catch(UnexpectedValueException $e) {
            Log::error($e->getMessage());
            // Invalid payload
            return $this->invalidMethod();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->invalidMethod();
        }

        // Handle the event
        switch ($event) {
            case 'transaction_created':
                $this->handleTrackCreated($shippoRequest['data']);
                break;
            case 'track_updated':
                $this->handleTrackUpdated($shippoRequest['data']);
                break;
            default:
                echo 'Received unknown event type ' . $event;
        }

        return $this->missingMethod();
    }

    /**
     * @param $data
     * @return void
     */
    protected function handleTrackCreated($data): void
    {
        Log::info('Shippo track created');
        $shipment = null;
        if (str_contains($data['metadata'], ':')) {
            [$typeShipment, $shipmentId] = explode(':', $data['metadata']);
            if ($typeShipment === 'customer_shipment') {
                $shipment = CustomerShipment::where('id', $shipmentId)->where('shippo_transaction_id', $data['object_id'])->first();
            }
        } else if ($data['transaction']) {
            $shipment = CustomerShipment::where('shippo_transaction_id', $data['transaction'])->first();
            if ($shipment === null) {
                $shipment = ManufacturerShipment::where('shippo_transaction_id', $data['transaction'])->first();
            }
        }

        if ($shipment) {
            $shipment->tracking_number = $shipment->tracking_number ?? $data['tracking_number'];
            $shipment->tracking_url = $shipment->tracking_url ?? $data['tracking_url'];
            $shipment->label_url = $shipment->label_url ?? $data['label_url'];
            $shipment->commercial_invoice_url = $shipment->commercial_invoice_url ?? $data['commercial_invoice_url'];
            $shipment->qr_code_url = $shipment->qr_code_url ?? $data['qr_code_url'] ?? null;
            $shipment->expected_delivery_date = $shipment->expected_delivery_date ?? $data['eta'];
            $shipment->shippo_transaction_meta_data = $data;
            $shipment->save();
        }
    }

    /**
     * @param $data
     * @return void
     */
    protected function handleTrackUpdated($data): void
    {
        Log::info('Shippo track updated');
        $shipment = null;
        if (str_contains($data['metadata'], ':')) {
            [$typeShipment, $shipmentId] = explode(':', $data['metadata']);
            if ($typeShipment === 'customer_shipment') {
                $shipment = CustomerShipment::where('id', $shipmentId)->where('shippo_transaction_id', $data['object_id'])->first();
            }
        } else if ($data['transaction']) {
            $shipment = CustomerShipment::where('shippo_transaction_id', $data['transaction'])->first();
            if ($shipment === null) {
                $shipment = ManufacturerShipment::where('shippo_transaction_id', $data['transaction'])->first();
            }
        }

        if ($shipment) {
            $shipment->trackingStatuses()->create([
                'object_id' => $data['tracking_status']['object_id'],
                'status' => $data['tracking_status']['status'],
                'sub_status' => $data['tracking_status']['substatus']['text'] ?? null,
                'status_details' => $data['tracking_status']['status_details'],
                'status_date' => Carbon::parse($data['tracking_status']['status_date']),
                'location' => $data['tracking_status']['location'],
                'meta_data' => $data,
            ]);

            if ($shipment instanceof CustomerShipment) {
                if ($data['tracking_status']['status'] === 'DELIVERED') {
                    $shipment->arrived_at = Carbon::parse($data['tracking_status']['status_date']);
                    $shipment->save();

                    $orderQueuesService = new OrderQueuesService();
                    $orders = [];
                    foreach ($shipment->orderQueues as $orderQueue) {
                        if (!array_key_exists($orderQueue->order_id, $orders)) {
                            $orders[$orderQueue->order_id] = $orderQueue->order;
                        }
                        $orderQueuesService->setStatus($orderQueue, 'completed');
                    }

                    // ToDo: If all order queues completed, update order in woocommerce to customer
                    foreach ($orders as $order) {
                        if ($order->allOrderQueuesEndStatus()) {
                            (new WoocommerceApiService())->updateOrderStatus($order->wp_id, 'completed');
                        }
                    }
                }
            } else if ($shipment instanceof ManufacturerShipment) {
                if ($data['tracking_status']['status'] === 'DELIVERED') {
                    $shipment->arrived_at = Carbon::parse($data['tracking_status']['status_date']);
                    $shipment->save();

                    $orderQueuesService = new OrderQueuesService();
                    foreach ($shipment->orderQueues as $orderQueue) {
                        $orderQueuesService->setStatus($orderQueue, 'at-dc');
                    }
                }
            }
        }
    }
}
