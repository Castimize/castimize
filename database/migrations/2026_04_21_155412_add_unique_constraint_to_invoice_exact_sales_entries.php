<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate rows caused by job retries, keeping the oldest entry
        // (lowest id) per (invoice_id, diary) combination. Covers both active
        // and soft-deleted rows so the unique constraint can be applied cleanly.
        DB::statement('
            DELETE FROM invoice_exact_sales_entries
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT MIN(id) AS id
                    FROM invoice_exact_sales_entries
                    GROUP BY invoice_id, diary
                ) AS keep
            )
        ');

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
