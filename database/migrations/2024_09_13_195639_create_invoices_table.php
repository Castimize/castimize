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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->string('invoice_number')->index();
            $table->dateTime('invoice_date');
            $table->boolean('debit')->default(true);
            $table->float('total_amount');
            $table->string('currency_code')->default('USD');
            $table->text('description');
            $table->string('email');
            $table->string('email_cc')->nullable();
            $table->string('contact_person');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('house_number');
            $table->string('postal_code');
            $table->string('city');
            $table->string('country');
            $table->string('vat')->default(21);
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->string('vat_number')->nullable();
            $table->boolean('sent')->default(false)->index();
            $table->dateTime('sent_at')->nullable();
            $table->boolean('paid')->default(false)->index();
            $table->dateTime('paid_at')->nullable();
            $table->string('exact_online_guid')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('invoices', function (Blueprint $table) {
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
        Schema::dropIfExists('invoices');
    }
};
