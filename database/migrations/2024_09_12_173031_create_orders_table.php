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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->unsignedBigInteger('customer_shipment_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->integer('wp_id')->nullable()->index();
            $table->string('order_number')->index();
            $table->string('order_key')->nullable();
            $table->string('status')->default('processing');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();
            $table->string('billing_phone_number')->nullable();
            $table->string('billing_address_line1')->nullable();
            $table->string('billing_address_line2')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_vat_number')->nullable();
            $table->string('shipping_first_name')->nullable();
            $table->string('shipping_last_name')->nullable();
            $table->string('shipping_company')->nullable();
            $table->string('shipping_phone_number')->nullable();
            $table->string('shipping_address_line1')->nullable();
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_country')->nullable();
            $table->float('order_product_value');
            $table->unsignedBigInteger('service_id')->nullable()->index();
            $table->float('service_fee')->nullable();
            $table->float('service_fee_tax')->nullable();
            $table->float('shipping_fee')->nullable();
            $table->float('shipping_fee_tax')->nullable();
            $table->float('discount_fee')->nullable();
            $table->float('discount_fee_tax')->nullable();
            $table->float('total')->nullable();
            $table->float('total_tax')->nullable();
            $table->float('production_cost')->nullable();
            $table->float('production_cost_tax')->nullable();
//            $table->float('logistic_cost')->nullable();
            $table->string('currency_code')->default('USD');
            $table->integer('order_parts')->default(1);
            $table->string('payment_method')->nullable();
            $table->string('payment_issuer')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->string('customer_ip_address')->nullable();
            $table->string('customer_user_agent')->nullable();
            $table->json('meta_data')->nullable();
            $table->text('comments')->nullable();
            $table->string('promo_code')->nullable();
            $table->dateTime('fast_delivery_lead_time')->nullable();
            $table->boolean('is_paid')->default(0);
            $table->dateTime('paid_at')->nullable();
            $table->integer('order_customer_lead_time')->default(1);
            $table->dateTime('due_date')->index()->nullable();
            $table->dateTime('arrived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('customer_shipment_id')->references('id')->on('customer_shipments')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
        });

        Schema::table('orders', function (Blueprint $table) {
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
        Schema::dropIfExists('orders');
    }
};
