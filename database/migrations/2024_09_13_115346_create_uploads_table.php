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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('material_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->string('name');
            $table->string('file_name');
            $table->string('material_name')->nullable();
            $table->float('model_volume_cc');
            $table->float('model_x_length');
            $table->float('model_y_length');
            $table->float('model_z_length');
            $table->float('model_box_volume');
            $table->float('model_surface_area_cm2');
            $table->integer('model_parts')->default(1);
            $table->integer('quantity')->default(1);
            $table->float('subtotal')->nullable();
            $table->float('subtotal_tax')->nullable();
            $table->float('total')->nullable();
            $table->float('total_tax')->nullable();
            $table->string('currency_code')->default('EUR');
            $table->integer('customer_lead_time')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('orders')->onDelete('set null');
        });

        Schema::table('uploads', function (Blueprint $table) {
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
        Schema::dropIfExists('uploads');
    }
};
