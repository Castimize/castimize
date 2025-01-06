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
        Schema::create('currency_history_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('USD');
            $table->string('convert_currency', 3);
            $table->float('rate');
            $table->date('historical_date');
            $table->string('exact_online_guid')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['historical_date', 'base_currency', 'convert_currency'], 'historical_date_base_currency_convert_currency_index');
        });

        Schema::table('currency_history_rates', function (Blueprint $table) {
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
        Schema::dropIfExists('currency_history_rates');
    }
};
