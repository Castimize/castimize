<?php

namespace App\Console\Commands\Temp;

use App\Models\Customer;
use App\Services\Admin\CustomersService;
use Exception;
use Illuminate\Console\Command;

class FixCustomerAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-customer-addresses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fill addresses from customer woocommerce api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customersService = new CustomersService();
//        $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find(866);
//        dd($wpCustomer);
        $customers = Customer::whereNotNull('wp_id')->orderByDesc('id')->get();
        $totalCustomers = $customers->count();
        $progressBar = $this->output->createProgressBar($totalCustomers);

        $this->info("Updating $totalCustomers customers from Woocommerce");
        $progressBar->start();


        foreach ($customers as $customer) {
            $this->info("Updating $customer->wp_id");
            try {
                $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($customer->wp_id);
                $customersService->storeCustomerFromWpCustomer($wpCustomer);
            } catch (Exception $e) {
                $this->info($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        return true;
    }
}
