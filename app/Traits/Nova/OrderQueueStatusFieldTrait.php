<?php

namespace App\Traits\Nova;

use WesselPerik\StatusField\StatusField;

trait OrderQueueStatusFieldTrait
{
    /**
     * @return StatusField
     */
    protected function getStatusField(): StatusField
    {
//                        In queue 🚦
//                        Rejection request ❎
//                        Cancelled ❌
//                        In production 🛠️
//                        Available for shipping ⚓️
//                        In transit to DC 🚢
//                        At DC 🏭
//                        In transit to customer 📦
//                        Completed ✔
//                        Reprinted 🔙

        return StatusField::make(__('Status'))
            ->icons([
                'dots-circle-horizontal' => $this->status === 'in-queue',
                'x-circle' => $this->status === 'rejection-request',
                'x' => $this->status === 'cancelled',
                'cog' => $this->status === 'in-production',
                'clipboard-check' => $this->status === 'available-for-shipping',
                'truck' => $this->status === 'in-transit-to-dc',
                'office-building' => $this->status === 'at-dc',
                'badge-check' => $this->status === 'in-transit-to-customer',
                'check' => $this->status === 'completed',
                'printer' => $this->status === 'reprinted',
            ])
            ->tooltip([
                'dots-circle-horizontal' => __('In queue'),
                'x-circle' => __('Rejection request'),
                'x' => __('Cancelled'),
                'cog' => __('In production'),
                'clipboard-check' => __('Available for shipping'),
                'truck' => __('In transit to DC'),
                'office-building' => __('At DC'),
                'badge-check' => __('In transit to customer'),
                'check' => __('Completed'),
                'printer' => __('Reprinted'),
            ])
            ->color([
                'dots-circle-horizontal' => 'grey-500',
                'x-circle' => 'orange-500',
                'x' => 'red-500',
                'cog' => 'yellow-500',
                'clipboard-check' => 'yellow-500',
                'truck' => 'yellow-500',
                'office-building' => 'yellow-500',
                'badge-check' => 'yellow-500',
                'check' => 'green-500',
                'printer' => 'purple-500',
            ]);
    }

    /**
     * @return StatusField
     */
    protected function getStatusCheckField(): StatusField
    {
        //Final arrival date = Date ordered + customer_lead_time
        //In queue
        //Target date: date orderded + 1 business day
        //Rejection request
        //Target date: rejections.created_at + 1 business day
        //in production
        //Target date: contract-date
        //available for shipping
        //Dichstbijzijnde datum van:
        //OF: Target date: Final arrival date - shipping_fees.default_lead_time - 1 business day - manufacturing_costs.shipment_lead_time
        //OF: available for shipping + 2 business days
        //in_transit_to_dc
        //Dichtstbijzijnde datum van:
        //OF: Target date: Final arrival date - shipping_fees.default_lead_time - 1 business day
        //OF: manufacturing.shipments.sent_at + manufacturing_costs.shipment_lead_time
        //at_dc
        //Target date: Final arrival date - shipping_fees.default_lead_time
        //In transit to customer
        //Target date: Final arrival date

        return StatusField::make(__('Status'))
            ->icons([
                'check' => $this->onSchedule,
                'clock' => !$this->onSchedule,
            ])
            ->tooltip([
                'check' => __('On schedule'),
                'clock' => __('Behind schedule'),
            ])
            ->color([
                'check' => 'green-500',
                'clock' => 'red-500',
            ]);
    }
}
