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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id')->nullable()->index();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->float('setup_fee')->nullable();
            $table->float('setup_fee_amount')->nullable();
            $table->float('minimum_per_stl')->nullable();
            $table->float('price_minimum_per_stl')->nullable();
            $table->float('price_volume_cc')->nullable();
            $table->float('price_surface_cm2')->nullable();
            $table->float('fixed_fee_per_part')->nullable();
            $table->float('material_discount')->nullable();
            $table->float('bulk_discount')->nullable();
            $table->string('currency_code')->default('EUR');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('prices', function (Blueprint $table) {
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
        Schema::dropIfExists('prices');
    }
};
