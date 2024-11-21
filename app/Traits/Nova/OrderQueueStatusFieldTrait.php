<?php

namespace App\Traits\Nova;

use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Fields\Text;

trait OrderQueueStatusFieldTrait
{
    /**
     * @return Text
     * @throws HelperNotSupported
     */
    protected function getStatusField(): Text
    {
//                        In queue 🚦
//                        Rejection request ❎
//                        Canceled ❌
//                        In production 🛠️
//                        Available for shipping ⚓️
//                        In transit to DC 🚢
//                        At DC 🏭
//                        In transit to customer 📦
//                        Completed ✔
//                        Reprinted 🔙

        return Text::make(__('Status'), function () {
            return match ($this->status_slug) {
                'in-queue' => '<span data-toggle="tooltip" data-placement="top" title="' . __('In queue') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>',
                'rejection-request' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Rejection request') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(249 115 22)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>',
                'canceled' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Canceled') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(239 68 68)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></span>',
                'in-production' => '<span data-toggle="tooltip" data-placement="top" title="' . __('In production') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>',
                'available-for-shipping' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Available for shipping') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>',
                'in-transit-to-dc' => '<span data-toggle="tooltip" data-placement="top" title="' . __('In transit to DC') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg></span>',
                'at-dc' => '<span data-toggle="tooltip" data-placement="top" title="' . __('At DC') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>',
                'in-transit-to-customer' => '<span data-toggle="tooltip" data-placement="top" title="' . __('In transit to customer') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(234 179 8)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg></span>',
                'reprinted' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Reprinted') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(99 102 241)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg></span>',
                'completed' => '<span data-toggle="tooltip" data-placement="top" title="' . __('Completed') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(34 197 94)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>',
                default => '',
            };
        })
            ->hideOnExport()
            ->asHtml();
    }

    /**
     * @return Text
     * @throws HelperNotSupported
     */
    protected function getStatusCheckField(): Text
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

        return Text::make(__('On schedule'), function () {
            if ($this->on_schedule) {
                return '<span data-toggle="tooltip" data-placement="top" title="' . __('On schedule') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(34 197 94)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>';
            }
            return '<span data-toggle="tooltip" data-placement="top" title="' . __('Behind schedule') . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="rgb(239 68 68)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
        })
            ->asHtml()
            ->hideOnExport()
            ->canSee(function () {
                return !in_array($this->getLastStatus()?->slug, ['canceled']);
            });
    }
}
