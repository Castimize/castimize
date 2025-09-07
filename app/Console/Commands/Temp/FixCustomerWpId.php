<?php

namespace App\Console\Commands\Temp;

use App\Models\Customer;
use Illuminate\Console\Command;

class FixCustomerWpId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-customer-wp-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to fill wp_id from customer woocommerce api';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $wpCustomers = \Codexshaper\WooCommerce\Facades\Customer::all();
        $totalCustomers = $wpCustomers->count();
        $progressBar = $this->output->createProgressBar($totalCustomers);

        $this->info("Updating $totalCustomers customers from Woocommerce");
        $progressBar->start();

        foreach ($wpCustomers as $wpCustomer) {
            $customer = Customer::where('email', $wpCustomer->email)->first();
            if ($customer) {
                $customer->wp_id = $wpCustomer->id;
                $customer->save();
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        return true;
    }
}
