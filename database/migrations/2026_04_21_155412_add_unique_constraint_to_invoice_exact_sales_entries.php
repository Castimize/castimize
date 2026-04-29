<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Picqer\Financials\Exact\SalesEntry;

return new class extends Migration
{
    public function up(): void
    {
        // Find all duplicate rows — keep the oldest (MIN id) per (invoice_id, diary).
        $duplicates = DB::select('
            SELECT id, exact_online_guid, invoice_id, diary
            FROM invoice_exact_sales_entries
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT MIN(id) AS id
                    FROM invoice_exact_sales_entries
                    GROUP BY invoice_id, diary
                ) AS keep
            )
            ORDER BY invoice_id, diary, id
        ');

        if (! empty($duplicates)) {
            $connection = app()->make('Exact\Connection');

            foreach ($duplicates as $duplicate) {
                // Try to delete from Exact Online first so we don't leave orphaned entries.
                try {
                    $salesEntry = new SalesEntry($connection);
                    $results = $salesEntry->filter("EntryID eq guid'{$duplicate->exact_online_guid}'");

                    if (count($results) > 0 && $results[0] instanceof SalesEntry) {
                        $results[0]->delete();
                        Log::info('Migration: deleted duplicate sales entry from Exact Online', [
                            'id' => $duplicate->id,
                            'exact_online_guid' => $duplicate->exact_online_guid,
                            'invoice_id' => $duplicate->invoice_id,
                            'diary' => $duplicate->diary,
                        ]);
                    } else {
                        Log::warning('Migration: duplicate sales entry not found in Exact Online (already removed?)', [
                            'id' => $duplicate->id,
                            'exact_online_guid' => $duplicate->exact_online_guid,
                            'invoice_id' => $duplicate->invoice_id,
                            'diary' => $duplicate->diary,
                        ]);
                    }
                } catch (Throwable $e) {
                    // Log and continue — we still remove the local record so the
                    // unique constraint can be applied. Manual cleanup in Exact may be needed.
                    Log::error('Migration: failed to delete duplicate sales entry from Exact Online', [
                        'id' => $duplicate->id,
                        'exact_online_guid' => $duplicate->exact_online_guid,
                        'invoice_id' => $duplicate->invoice_id,
                        'diary' => $duplicate->diary,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Always hard-delete the local duplicate record.
                DB::table('invoice_exact_sales_entries')->where('id', $duplicate->id)->delete();
            }
        }

        Schema::table('invoice_exact_sales_entries', function (Blueprint $table): void {
            $table->unique(['invoice_id', 'diary']);
        });
    }

    public function down(): void
    {
        Schema::table('invoice_exact_sales_entries', function (Blueprint $table): void {
            $table->dropUnique(['invoice_id', 'diary']);
        });
    }
};
