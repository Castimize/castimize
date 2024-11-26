<?php

namespace App\Observers;

use App\Models\CustomerShipment;
use App\Models\OrderQueue;
use App\Services\Admin\ShippingService;
use http\Exception\RuntimeException;
use JsonException;

class CustomerShipmentObserver
{
    /**
     * Handle the CustomerShipment "creating" event.
     * @throws JsonException
     */
    public function creating(CustomerShipment $customerShipment): void
    {
        $customerShipment->fromAddress = [
            'name' => $customerShipment->from_address_name ?? '',
            'company' => $customerShipment->from_address_company ?? '',
            'address_line1' => $customerShipment->from_address_address_line1 ?? '',
            'address_line2' => $customerShipment->from_address_address_line2 ?? '',
            'postal_code' => $customerShipment->from_address_postal_code ?? '',
            'city' => $customerShipment->from_address_city ?? '',
            'state' => $customerShipment->from_address_state ?? '',
            'country' => $customerShipment->from_address_country ?? '',
            'phone' => $customerShipment->from_address_phone ?? '',
            'email' => $customerShipment->from_address_email ?? '',
        ];
        $customerShipment->toAddress = [
            'name' => $customerShipment->to_address_name ?? '',
            'company' => $customerShipment->to_address_company ?? '',
            'address_line1' => $customerShipment->to_address_address_line1 ?? '',
            'address_line2' => $customerShipment->to_address_address_line2 ?? '',
            'postal_code' => $customerShipment->to_address_postal_code ?? '',
            'city' => $customerShipment->to_address_city ?? '',
            'state' => $customerShipment->to_address_state ?? '',
            'country' => $customerShipment->to_address_country ?? '',
            'phone' => $customerShipment->to_address_phone ?? '',
            'email' => $customerShipment->to_address_email ?? '',
        ];
        $customerShipment->parcel = [
            'distance_unit' => $customerShipment->parcel_distance_unit ?? '',
            'length' => $customerShipment->parcel_length ?? '',
            'width' => $customerShipment->parcel_width ?? '',
            'height' => $customerShipment->parcel_height ?? '',
            'mass_unit' => $customerShipment->parcel_mass_unit ?? '',
            'weight' => $customerShipment->parcel_weight ?? '',
        ];
        unset(
            $customerShipment->from_address_name,
            $customerShipment->from_address_company,
            $customerShipment->from_address_address_line1,
            $customerShipment->from_address_address_line2,
            $customerShipment->from_address_postal_code,
            $customerShipment->from_address_city,
            $customerShipment->from_address_state,
            $customerShipment->from_address_country,
            $customerShipment->from_address_phone,
            $customerShipment->from_address_email,
            $customerShipment->to_address_name,
            $customerShipment->to_address_company,
            $customerShipment->to_address_address_line1,
            $customerShipment->to_address_address_line2,
            $customerShipment->to_address_postal_code,
            $customerShipment->to_address_city,
            $customerShipment->to_address_state,
            $customerShipment->to_address_country,
            $customerShipment->to_address_phone,
            $customerShipment->to_address_email,
            $customerShipment->parcel_distance_unit,
            $customerShipment->parcel_length,
            $customerShipment->parcel_width,
            $customerShipment->parcel_height,
            $customerShipment->parcel_mass_unit,
            $customerShipment->parcel_weight
        );
        $selectedPOs = json_decode($customerShipment->selectedPOs, true, 512, JSON_THROW_ON_ERROR);
        if (count($selectedPOs) > 0) {
            $orderQueues = OrderQueue::with(['order', 'upload'])->whereIn('id', $selectedPOs)->get();
            $customerShipment->selectedPOs = $orderQueues;
            $customerShipment->currency_id = $orderQueues->first()->order->currency_id;
            $customerShipment->currency_code = $orderQueues->first()->order->currency_code;
            $totalParts = 0;
            $totalCosts = 0;
            foreach ($orderQueues as $orderQueue) {
                $totalParts += $orderQueue->upload->model_parts;
                $totalCosts += $orderQueue->upload->total;
            }
            $customerShipment->total_parts = $totalParts;
            $customerShipment->total_costs = $totalCosts;
        }
    }

    /**
     * @param CustomerShipment $customerShipment
     * @return void
     */
    public function created(CustomerShipment $customerShipment): void
    {
        if ($customerShipment->selectedPOs) {
            foreach ($customerShipment->selectedPOs as $selectedPO) {
                $selectedPO->customer_shipment_id = $customerShipment->id;
                $selectedPO->save();
            }

            $shippingService = app(ShippingService::class);
            $response = $shippingService->createShippoCustomerShipment($customerShipment);

            if ($response['transaction'] && $response['transaction']['status'] === 'SUCCESS') {
                $customerShipment->shippo_shipment_id = $response['shipment']['object_id'];
                $customerShipment->shippo_shipment_meta_data = $response['shipment'];
                $customerShipment->expected_delivery_date = $response['transaction']['eta'] ?? null;
                $customerShipment->tracking_number = $response['transaction']['tracking_number'];
                $customerShipment->tracking_url = $response['transaction']['tracking_url_provider'];
                $customerShipment->shippo_transaction_id = $response['transaction']['object_id'];
                $customerShipment->shippo_transaction_meta_data = $response['transaction'];
                $customerShipment->label_url = $response['transaction']['label_url'];
                $customerShipment->commercial_invoice_url = $response['transaction']['commercial_invoice_url'];
                $customerShipment->qr_code_url = $response['transaction']['qr_code_url'];

                $customerShipment->save();
            }
        }
    }

//    public function deleting(CustomerShipment $customerShipment): void
//    {
//        foreach ($customerShipment->orderQueues as $orderQueue) {
//            $hasEndStatus = [];
//            /** @var $orderQueue OrderQueue */
//            if ($orderQueue->getLastStatus()->end_status) {
//                $hasEndStatus[] = $orderQueue->id;
//            }
//
//            if (count($hasEndStatus) > 0) {
//                throw new RuntimeException(__('You cannot delete this customer shipment, because it contains PO\'s which have an end status.'));
//            }
//        }
//    }

    public function deleted(CustomerShipment $customerShipment): void
    {
       $customerShipment->orderQueues()->update(['customer_shipment_id' => null]);
    }
}
