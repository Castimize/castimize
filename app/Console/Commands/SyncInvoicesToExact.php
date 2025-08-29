<?php

namespace App\Console\Commands;

use App\Jobs\SyncCustomerToExact;
use App\Jobs\SyncInvoiceToExact;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

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
    protected $description = 'Command to sync invoices to Exact';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $invoices = Invoice::with(['customer', 'lines'])
//            ->where('id', 22)
//            ->whereHas('exactSalesEntries', operator: '=', count: 1)
//            ->whereHas('exactSalesEntries', function ($query) {
//                $query->where('created_at', '<', '2025-01-07 08:28:21');
//            })
            ->doesntHave('exactSalesEntries')
            ->orderBy('invoice_number')
            ->get();

        $count = $invoices->count();
        $progressBar = $this->output->createProgressBar($count);
        $this->info("Syncing $count invoices to Exact");
        $progressBar->start();

        foreach ($invoices as $invoice) {
            // (new ExactOnlineService())->deleteSyncedInvoice($invoice);

            //            (new ExactOnlineService())->syncInvoice($invoice);
            //            if ($invoice->paid) {
            //                (new ExactOnlineService())->syncInvoicePaid($invoice);
            //            }
            Bus::chain([
                new SyncCustomerToExact($invoice->customer->wp_id),
                new SyncInvoiceToExact($invoice, $invoice->customer->wp_id, true),
            ])
                ->onQueue('exact')
                ->dispatch();

            sleep(8);

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
