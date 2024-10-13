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
        Schema::create('order_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id')->nullable()->index();
            $table->unsignedBigInteger('upload_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('shipping_fee_id')->nullable()->index();
            $table->unsignedBigInteger('manufacturer_shipment_id')->nullable()->index();
            $table->unsignedBigInteger('manufacturer_cost_id')->nullable()->index();
            $table->unsignedBigInteger('customer_shipment_id')->nullable()->index();
            $table->dateTime('due_date')->index()->nullable();
            $table->dateTime('final_arrival_date')->index();
            $table->dateTime('contract_date')->index()->nullable();
            $table->float('manufacturer_costs')->nullable();
            $table->boolean('status_manual_changed')->default(false)->index()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('set null');
            $table->foreign('upload_id')->references('id')->on('uploads')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('shipping_fee_id')->references('id')->on('shipping_fees')->onDelete('cascade');
            $table->foreign('manufacturer_shipment_id')->references('id')->on('manufacturer_shipments')->onDelete('set null');
            $table->foreign('manufacturer_cost_id')->references('id')->on('manufacturer_costs')->onDelete('set null');
            $table->foreign('customer_shipment_id')->references('id')->on('customer_shipments')->onDelete('set null');
        });

        Schema::table('order_queue', function (Blueprint $table) {
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
        Schema::dropIfExists('order_queue');
    }
};
