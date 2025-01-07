<?php

namespace App\Console\Commands\Temp;

use App\Jobs\SyncCustomerToExact;
use App\Jobs\SyncExchangeRateToExact;
use App\Jobs\SyncInvoiceToExact;
use App\Models\CurrencyHistoryRate;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Admin\LogRequestService;
use App\Services\Exact\ExactOnlineService;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncInvoicesToExact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:sync-invoices-to-exact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to sync invoices to Exact';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoices = Invoice::with(['customer', 'lines'])
//            ->doesntHave('exactSalesEntries')
            ->get();

        $count = $invoices->count();
        $progressBar = $this->output->createProgressBar($count);
        $this->info("Syncing $count invoices to Exact");
        $progressBar->start();

        foreach ($invoices as $invoice) {
            Bus::chain([
//                new SyncCustomerToExact($invoice->customer->wp_id),
                new SyncInvoiceToExact($invoice, $invoice->customer->wp_id, true),
            ])
                ->onQueue('exact')
                ->dispatch();

            sleep(5);

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
