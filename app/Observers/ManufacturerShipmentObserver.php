<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\ManufacturerShipment;
use App\Models\OrderQueue;
use App\Nova\Settings\Shipping\DcSettings;
use App\Services\Admin\OrderQueuesService;
use App\Services\Admin\ShippingService;
use Exception;

class ManufacturerShipmentObserver
{
    /**
     * Handle the ManufacturerShipment "creating" event.
     */
    public function creating(ManufacturerShipment $manufacturerShipment): void
    {
        $dcSettings = (new DcSettings());
        $manufacturerShipment->fromAddress = [
            'name' => $manufacturerShipment->from_address_name ?? '',
            'company' => $manufacturerShipment->from_address_company ?? '',
            'address_line1' => $manufacturerShipment->from_address_address_line1 ?? '',
            'address_line2' => $manufacturerShipment->from_address_address_line2 ?? '',
            'postal_code' => $manufacturerShipment->from_address_postal_code ?? '',
            'city' => $manufacturerShipment->from_address_city ?? '',
            'state' => $manufacturerShipment->from_address_state ?? '',
            'country' => $manufacturerShipment->from_address_country ?? '',
            'phone' => $manufacturerShipment->from_address_phone ?? '',
            'email' => $manufacturerShipment->from_address_email ?? '',
        ];
        $manufacturerShipment->toAddress = [
            'name' => $dcSettings->name,
            'company' => $dcSettings->company,
            'address_line1' => $dcSettings->addressLine1,
            'address_line2' => $dcSettings->addressLine2,
            'postal_code' => $dcSettings->postalCode,
            'city' => $dcSettings->city,
            'state' => $dcSettings->state,
            'country' => $dcSettings->country,
            'phone' => $dcSettings->phone,
            'email' => $dcSettings->email,
        ];
        $manufacturerShipment->parcel = [
            'distance_unit' => $manufacturerShipment->parcel_distance_unit ?? '',
            'length' => $manufacturerShipment->parcel_length ?? '',
            'width' => $manufacturerShipment->parcel_width ?? '',
            'height' => $manufacturerShipment->parcel_height ?? '',
            'mass_unit' => $manufacturerShipment->parcel_mass_unit ?? '',
            'weight' => $manufacturerShipment->parcel_weight ?? '',
        ];
        unset(
            $manufacturerShipment->from_address_name,
            $manufacturerShipment->from_address_company,
            $manufacturerShipment->from_address_address_line1,
            $manufacturerShipment->from_address_address_line2,
            $manufacturerShipment->from_address_postal_code,
            $manufacturerShipment->from_address_city,
            $manufacturerShipment->from_address_state,
            $manufacturerShipment->from_address_country,
            $manufacturerShipment->from_address_phone,
            $manufacturerShipment->from_address_email,
            $manufacturerShipment->to_address_name,
            $manufacturerShipment->to_address_company,
            $manufacturerShipment->to_address_address_line1,
            $manufacturerShipment->to_address_address_line2,
            $manufacturerShipment->to_address_postal_code,
            $manufacturerShipment->to_address_city,
            $manufacturerShipment->to_address_state,
            $manufacturerShipment->to_address_country,
            $manufacturerShipment->to_address_phone,
            $manufacturerShipment->to_address_email,
            $manufacturerShipment->parcel_distance_unit,
            $manufacturerShipment->parcel_length,
            $manufacturerShipment->parcel_width,
            $manufacturerShipment->parcel_height,
            $manufacturerShipment->parcel_mass_unit,
            $manufacturerShipment->parcel_weight
        );
        $selectedPOs = json_decode($manufacturerShipment->selectedPOs, true, 512, JSON_THROW_ON_ERROR);
        if (count($selectedPOs) === 0) {
            throw new Exception('Please select PO\'s');
        }

        $orderQueues = OrderQueue::with(['order', 'upload'])->whereIn('id', $selectedPOs)->get();
        $manufacturerShipment->selectedPOs = $orderQueues;
        $manufacturerShipment->currency_id = $orderQueues->first()->order->currency_id;
        $manufacturerShipment->currency_code = $orderQueues->first()->order->currency_code;
        $totalParts = 0;
        $totalCosts = 0;
        foreach ($orderQueues as $orderQueue) {
            $totalParts += $orderQueue->upload->model_parts;
            $totalCosts += $orderQueue->manufacturer_costs;
        }
        $manufacturerShipment->total_parts = $totalParts;
        $manufacturerShipment->total_costs = $totalCosts;

    }

    /**
     * @param ManufacturerShipment $manufacturerShipment
     * @return void
     */
    public function created(ManufacturerShipment $manufacturerShipment): void
    {
        $orderQueuesService = new OrderQueuesService();
        if ($manufacturerShipment->selectedPOs) {
            foreach ($manufacturerShipment->selectedPOs as $selectedPO) {
                $selectedPO->manufacturer_shipment_id = $manufacturerShipment->id;
                $selectedPO->save();
                $orderQueuesService->setStatus($selectedPO, 'in-transit-to-dc');
            }

            if (!$manufacturerShipment->handles_own_shipping) {
                $shippingService = app(ShippingService::class);
                $response = $shippingService->createShippoManufacturerShipment($manufacturerShipment);

                if ($response['transaction'] && $response['transaction']['status'] === 'SUCCESS') {
                    $manufacturerShipment->shippo_shipment_id = $response['shipment']['object_id'];
                    $manufacturerShipment->shippo_shipment_meta_data = $response['shipment'];
                    $manufacturerShipment->expected_delivery_date = $response['shipment']['eta'];
                    $manufacturerShipment->tracking_number = $response['transaction']['tracking_number'];
                    $manufacturerShipment->tracking_url = $response['transaction']['tracking_url_provider'];
                    $manufacturerShipment->shippo_transaction_id = $response['transaction']['object_id'];
                    $manufacturerShipment->shippo_transaction_meta_data = $response['transaction'];
                    $manufacturerShipment->label_url = $response['transaction']['label_url'];
                    $manufacturerShipment->commercial_invoice_url = $response['transaction']['commercial_invoice_url'];
                    $manufacturerShipment->qr_code_url = $response['transaction']['qr_code_url'];

                    $manufacturerShipment->save();
                }
            }
        }
    }
}
