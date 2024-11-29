<?php

namespace App\Console\Commands\Temp;

use App\Models\Customer;
use App\Models\CustomerShipment;
use App\Models\ManufacturerShipment;
use App\Models\Order;
use Illuminate\Console\Command;

class FixExpectedDeliveryDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-expected-delivery-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fill expected delivery date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = ManufacturerShipment::with('trackingStatuses')
            ->withTrashed()
            ->whereNull('expected_delivery_date');
        $countManuShipments = $query->count();
        $manufacturerShipments = $query->get();
        $query = CustomerShipment::with('trackingStatuses')
            ->withTrashed()
            ->whereNull('expected_delivery_date');
        $countCustShipments = $query->count();
        $customerShipments = $query->get();

        $count = $countManuShipments + $countCustShipments;
        $progressBar = $this->output->createProgressBar();
        $this->info("Updating $count shipments");
        $progressBar->start();

        foreach ($manufacturerShipments as $manufacturerShipment) {
            $trackingStatus = $manufacturerShipment->trackingStatuses->where('status', 'TRANSIT')->first();
            if ($trackingStatus) {
                $manufacturerShipment->expected_delivery_date = $trackingStatus->meta_data['eta'];
                $manufacturerShipment->save();
            }

            $progressBar->advance();
        }
        foreach ($customerShipments as $customerShipment) {
            $trackingStatus = $customerShipment->trackingStatuses->where('status', 'TRANSIT')->first();
            if ($trackingStatus) {
                $customerShipment->expected_delivery_date = $trackingStatus->meta_data['eta'];
                $customerShipment->save();
            }


            $progressBar->advance();
        }
        $progressBar->finish();

        return true;
    }
}
