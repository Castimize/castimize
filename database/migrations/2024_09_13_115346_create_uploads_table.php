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
            $table->unsignedBigInteger('manufacturer_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->string('name');
            $table->string('file_name');
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('material_name')->nullable();
            $table->string('manufacturer_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->float('model_volume_cc');
            $table->float('model_x_length');
            $table->float('model_y_length');
            $table->float('model_z_length');
            $table->float('model_box_volume');
            $table->float('model_surface_area_cm2');
            $table->integer('model_parts')->default(1);
            $table->boolean('exceeds_volume')->default(false);
            $table->boolean('exceeds_number_of_parts')->default(false);
            $table->boolean('exceeds_file_size')->default(false);
            $table->float('price')->nullable();
            $table->string('currency_code')->default('EUR');
            $table->integer('customer_lead_time')->nullable();
//            $table->float('bulk_discount_price')->nullable();
//            $table->dateTime('in_queue')->nullable();
//            $table->dateTime('accepted_at')->nullable();
//            $table->dateTime('rejected_at')->nullable();
//            $table->text('rejection_reason')->nullable();
//            $table->dateTime('reprint_at')->nullable();
//            $table->dateTime('available_for_shipping')->nullable();
//            $table->dateTime('in_shipping')->nullable();
//            $table->unsignedBigInteger('manufacturer_shipment_id')->nullable()->index();
//            $table->unsignedBigInteger('customer_shipment_id')->nullable()->index();
//            $table->dateTime('in_transit_to_dc')->nullable();
//            $table->dateTime('contract_date')->nullable();
//            $table->dateTime('arrived_at')->nullable();
//            $table->float('manufacturing_costs')->nullable();
//            $table->boolean('model_minimum')->default(false);
//            $table->float('bulk_discount_costs')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('set null');
            $table->foreign('manufacturer_shipment_id')->references('id')->on('manufacturer_shipments')->onDelete('set null');
            $table->foreign('customer_shipment_id')->references('id')->on('customer_shipments')->onDelete('set null');
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
