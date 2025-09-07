<?php

namespace App\Console\Commands\Temp;

use App\Models\Customer;
use Exception;
use Illuminate\Console\Command;

class FixCustomerNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-customer-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fill names from customer woocommerce api';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        //        $wpCustomers = \Codexshaper\WooCommerce\Facades\Customer::all();
        $customers = Customer::whereNotNull('wp_id')->orderByDesc('id')->get();
        $totalCustomers = $customers->count();
        $progressBar = $this->output->createProgressBar($totalCustomers);

        $this->info("Updating $totalCustomers customers from Woocommerce");
        $progressBar->start();

        foreach ($customers as $customer) {
            $this->info("Updating $customer->wp_id");
            try {
                $vatNumber = null;
                $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($customer->wp_id);
                foreach ($wpCustomer['meta_data'] as $metaData) {
                    if ($metaData->key === 'billing_eu_vat_number' && ! empty($metaData->value)) {
                        $vatNumber = $metaData->value;
                    }
                }

                $customer->first_name = $wpCustomer['first_name'] ?: null;
                $customer->last_name = $wpCustomer['last_name'] ?: null;
                $customer->company = $wpCustomer['billing']->company ?: null;
                $customer->phone = $wpCustomer['billing']->phone ?: null;
                $customer->vat_number = $vatNumber;
                $customer->save();
            } catch (Exception $e) {
                $this->info($e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        return true;
    }
}
