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
        Schema::create('manufacturer_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id')->nullable()->index();
            $table->unsignedBigInteger('material_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->integer('production_lead_time')->nullable();
            $table->integer('shipment_lead_time')->nullable();
            $table->float('setup_fee')->nullable();
            $table->float('setup_fee_amount')->nullable();
            $table->float('costs_volume_cc')->nullable();
            $table->float('costs_minimum_per_stl')->nullable();
            $table->float('costs_surface_cm2')->nullable();
            $table->string('currency_code')->default('EUR');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('manufacturer_costs', function (Blueprint $table) {
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
        Schema::dropIfExists('manufacturer_costs');
    }
};
