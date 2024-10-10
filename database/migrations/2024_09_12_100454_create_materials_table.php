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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_group_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->integer('wp_id')->nullable();
            $table->string('name');
            $table->float('discount')->nullable();
            $table->float('bulk_discount_10')->nullable();
            $table->float('bulk_discount_25')->nullable();
            $table->float('bulk_discount_50')->nullable();
            $table->integer('dc_lead_time')->nullable();
            $table->integer('fast_delivery_lead_time')->nullable();
            $table->float('fast_delivery_fee')->nullable();
            $table->string('currency_code')->nullable();
            $table->text('hs_code_description')->nullable();
            $table->string('hs_code')->nullable();
            $table->tinyText('article_eu_description')->nullable();
            $table->tinyText('article_us_description')->nullable();
            $table->string('tariff_code_eu')->nullable();
            $table->string('tariff_code_us')->nullable();
            $table->float('minimum_x_length')->nullable();
            $table->float('maximum_x_length')->nullable();
            $table->float('minimum_y_length')->nullable();
            $table->float('maximum_y_length')->nullable();
            $table->float('minimum_z_length')->nullable();
            $table->float('maximum_z_length')->nullable();
            $table->float('minimum_volume')->nullable();
            $table->float('maximum_volume')->nullable();
            $table->float('minimum_box_volume')->nullable();
            $table->float('maximum_box_volume')->nullable();
            $table->float('density')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('material_group_id')->references('id')->on('material_groups')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('materials', function (Blueprint $table) {
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
        Schema::dropIfExists('materials');
    }
};
