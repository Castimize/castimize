<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_exact_sales_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->index();
            $table->string('exact_online_guid');
            $table->string('diary');
            $table->json('exact_data');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('CASCADE');
        });

        Schema::table('invoice_exact_sales_entries', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable()->default(null)->after('created_at');
            $table->unsignedInteger('updated_by')->nullable()->default(null)->after('updated_at');
            $table->unsignedInteger('deleted_by')->nullable()->default(null)->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_exact_sales_entries');
    }
};
