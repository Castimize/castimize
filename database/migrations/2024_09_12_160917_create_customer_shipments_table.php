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
        Schema::create('customer_shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('arrived_at')->nullable();
            $table->integer('time_in_transit')->nullable();
            $table->dateTime('expected_delivery_date')->nullable();
            $table->string('ups_tracking', 500)->nullable();
            $table->tinyText('ups_tracking_manual')->nullable();
            $table->float('amount')->nullable();
            $table->integer('type')->nullable();
            $table->boolean('ups_service')->default(false);
            $table->dateTime('service_lead_time')->nullable();
            $table->float('service_costs')->nullable();
            $table->string('currency_code')->default('EUR');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('customer_shipments', function (Blueprint $table) {
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
        Schema::dropIfExists('customer_shipments');
    }
};