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
        Schema::create('shipping_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('logistics_zone_id')->index()->nullable();
            $table->unsignedBigInteger('currency_id')->index()->nullable();
            $table->string('name');
            $table->float('default_rate');
            $table->string('currency_code')->default('EUR');
            $table->integer('default_lead_time');
            $table->float('cc_threshold_1')->nullable();
            $table->float('rate_increase_1')->nullable();
            $table->float('cc_threshold_2')->nullable();
            $table->float('rate_increase_2')->nullable();
            $table->float('cc_threshold_3')->nullable();
            $table->float('rate_increase_3')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('logistics_zone_id')->references('id')->on('logistics_zones')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('shipping_fees', function (Blueprint $table) {
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
        Schema::dropIfExists('shipping_fees');
    }
};
