<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Picqer\Financials\Exact\SalesEntry;
use Throwable;

class CleanupExactDuplicateSalesEntries extends Command
{
    protected $signature = 'castimize:cleanup-exact-duplicate-sales-entries
                            {invoice_ids?* : One or more invoice IDs (optional — omit to process all synced invoices)}';

    protected $description = 'Remove sales entries from Exact Online that are no longer present in the local invoice_exact_sales_entries table';

    public function handle(): int
    {
        $invoiceIds = $this->argument('invoice_ids');

        $query = Invoice::with('exactSalesEntries')->has('exactSalesEntries');

        if (! empty($invoiceIds)) {
            $query->whereIn('id', $invoiceIds);

            $notFound = array_diff($invoiceIds, $query->pluck('id')->map(fn ($id) => (string) $id)->toArray());
            foreach ($notFound as $missingId) {
                $this->error("Invoice ID {$missingId} not found in database — skipping.");
                Log::channel('exact')->warning('CleanupExactDuplicateSalesEntries: invoice not found', ['invoice_id' => $missingId]);
            }
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->warn('No invoices found to process.');

            return self::FAILURE;
        }

        $this->info("Processing {$invoices->count()} invoice(s)...");

        $connection = app()->make('Exact\Connection');
        $totalDeleted = 0;
        $totalFailed = 0;

        foreach ($invoices as $invoice) {
            $this->line("Processing invoice #{$invoice->invoice_number} (ID: {$invoice->id})...");
            Log::channel('exact')->info('CleanupExactDuplicateSalesEntries: processing invoice', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            // GUIDs we want to KEEP — whatever is still in our local table.
            $localGuids = $invoice->exactSalesEntries->pluck('exact_online_guid')->all();
            $this->line('  Local entries (keep): '.($localGuids ? implode(', ', $localGuids) : 'none'));

            // Fetch all Exact entries for this invoice number.
            try {
                $salesEntryClient = new SalesEntry($connection);
                $exactEntries = $salesEntryClient->filter("YourRef eq '{$invoice->invoice_number}'");
            } catch (Throwable $e) {
                $this->error("  Could not fetch entries from Exact Online: {$e->getMessage()}");
                Log::channel('exact')->error('CleanupExactDuplicateSalesEntries: failed to fetch from Exact', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            if (empty($exactEntries)) {
                $this->line('  No entries found in Exact Online.');
                Log::channel('exact')->info('CleanupExactDuplicateSalesEntries: no entries in Exact Online', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);

                continue;
            }

            $this->line('  Exact entries found: '.count($exactEntries));

            foreach ($exactEntries as $exactEntry) {
                $guid = $exactEntry->EntryID;
                $journal = $exactEntry->Journal ?? '?';

                if (in_array($guid, $localGuids, true)) {
                    $this->line("  [KEEP]   GUID {$guid} (journal {$journal}) — present in local table.");

                    continue;
                }

                // Not in local table → orphan, delete from Exact.
                $this->warn("  [DELETE] GUID {$guid} (journal {$journal}) — not in local table, removing from Exact.");
                Log::channel('exact')->info('CleanupExactDuplicateSalesEntries: deleting orphaned entry from Exact', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'exact_online_guid' => $guid,
                    'journal' => $journal,
                ]);

                try {
                    $exactEntry->delete();
                    $totalDeleted++;
                    $this->info("  [DONE]   GUID {$guid} deleted from Exact.");
                    Log::channel('exact')->info('CleanupExactDuplicateSalesEntries: deleted successfully', [
                        'invoice_id' => $invoice->id,
                        'exact_online_guid' => $guid,
                    ]);
                } catch (Throwable $e) {
                    $totalFailed++;
                    $this->error("  [FAILED] GUID {$guid}: {$e->getMessage()}");
                    Log::channel('exact')->error('CleanupExactDuplicateSalesEntries: failed to delete from Exact', [
                        'invoice_id' => $invoice->id,
                        'exact_online_guid' => $guid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->newLine();
        $this->info("Done. Deleted: {$totalDeleted}, Failed: {$totalFailed}.");
        Log::channel('exact')->info('CleanupExactDuplicateSalesEntries: finished', [
            'deleted' => $totalDeleted,
            'failed' => $totalFailed,
        ]);

        return $totalFailed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
