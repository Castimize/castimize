<?php

namespace App\Console\Commands\Temp;

use App\Jobs\SetOrderPaid;
use App\Services\Payment\Stripe\StripeService;
use Illuminate\Console\Command;

class FixSetOrderPaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-set-order-paid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to set order to paid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $paymentIntent = (new StripeService)->getPaymentIntent('pi_3QFQFD0004dOynzN0vbN8CLp');
        SetOrderPaid::dispatch($paymentIntent, null);

        return true;
    }
}
