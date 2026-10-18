<?php

namespace App\Console\Commands;

use App\Jobs\SyncInvoicePaidToExact;
use App\Models\Invoice;
use Illuminate\Console\Command;

class SyncMissingDiary90ToExact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:sync-missing-diary-90-to-exact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch SyncInvoicePaidToExact for invoices that have a diary 70 entry but are missing diary 90';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $invoices = Invoice::with('customer')
            ->whereHas('exactSalesEntries', function ($query) {
                $query->where('diary', 70);
            })
            ->whereDoesntHave('exactSalesEntries', function ($query) {
                $query->where('diary', 90);
            })
            ->orderBy('invoice_number')
            ->get();

        $count = $invoices->count();
        $progressBar = $this->output->createProgressBar($count);
        $this->info("Dispatching diary 90 sync for {$count} invoices");
        $progressBar->start();

        foreach ($invoices as $invoice) {
            SyncInvoicePaidToExact::dispatch($invoice, $invoice->customer->wp_id)
                ->onQueue('exact');

            sleep(8);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Done.');
    }
}
