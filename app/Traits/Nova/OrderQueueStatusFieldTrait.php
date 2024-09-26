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
                'x' => 'redd-500',
                'cog' => 'yellow-500',
                'clipboard-check' => 'yellow-500',
                'truck' => 'yellow-500',
                'office-building' => 'yellow-500',
                'badge-check' => 'yellow-500',
                'check' => 'green-500',
                'printer' => 'purple-500',
            ]);
    }
}
