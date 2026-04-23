<?php

namespace App\Console\Commands;

use App\Jobs\DeleteInvoiceFromExact as DeleteInvoiceFromExactJob;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteInvoiceFromExact extends Command
{
    protected $signature = 'castimize:delete-invoice-from-exact
                            {invoice_ids* : One or more local invoice IDs to delete from Exact Online}';

    protected $description = 'Delete invoice sales entries from Exact Online using the YourRef stored in local exact_data';

    public function handle(): int
    {
        $invoiceIds = $this->argument('invoice_ids');

        if (empty($invoiceIds)) {
            $this->error('Provide at least one invoice ID.');

            return self::FAILURE;
        }

        $invoices = Invoice::with('exactSalesEntries')
            ->has('exactSalesEntries')
            ->whereIn('id', $invoiceIds)
            ->get();

        $notFound = array_diff($invoiceIds, $invoices->pluck('id')->map(fn ($id) => (string) $id)->toArray());
        foreach ($notFound as $missingId) {
            $this->error("Invoice ID {$missingId} not found or has no Exact sales entries — skipping.");
            Log::channel('exact')->warning('DeleteInvoiceFromExact: invoice not found or no local entries', ['invoice_id' => $missingId]);
        }

        if ($invoices->isEmpty()) {
            $this->warn('No invoices with Exact sales entries found.');

            return self::FAILURE;
        }

        foreach ($invoices as $invoice) {
            $yourRefs = $invoice->exactSalesEntries
                ->map(fn ($entry) => data_get($entry->exact_data, 'YourRef'))
                ->filter()
                ->unique()
                ->values();

            if ($yourRefs->isEmpty()) {
                $this->warn("Invoice #{$invoice->invoice_number} (ID: {$invoice->id}): no YourRef found in exact_data — skipping.");
                Log::channel('exact')->warning('DeleteInvoiceFromExact: no YourRef in exact_data', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);

                continue;
            }

            foreach ($yourRefs as $yourRef) {
                DeleteInvoiceFromExactJob::dispatch($yourRef);
                $this->info("Invoice #{$invoice->invoice_number} (ID: {$invoice->id}): dispatched delete job for YourRef '{$yourRef}'.");
                Log::channel('exact')->info('DeleteInvoiceFromExact: dispatched job', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'your_ref' => $yourRef,
                ]);
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
