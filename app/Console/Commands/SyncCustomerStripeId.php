<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\Payment\Stripe\StripeService;
use Illuminate\Console\Command;

class SyncCustomerStripeId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:sync-customer-stripe-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to sync customer stripe id';

    /**
     * Execute the console command.
     */
    public function handle(StripeService $stripeService)
    {
        $customers = Customer::whereJsonDoesntContainKey('stripe_data->stripe_id')->whereNotNull('wp_id')->get();
        $totalCustomers = $customers->count();
        $progressBar = $this->output->createProgressBar($totalCustomers);

        $this->info("Syncing $totalCustomers customers with Stripe ID");
        $progressBar->start();

        foreach ($customers as $customer) {
            $stripeCustomer = $stripeService->getCustomers([
                'email' => $customer->email,
            ]);
            $stripeData = $customer->stripe_data ?? [];
            $stripeData['stripe_id'] = $stripeCustomer->first()?->id;
            $customer->stripe_data = $stripeData;
            $customer->save();

            $progressBar->advance();
        }

        $progressBar->finish();

        return true;
    }
}
