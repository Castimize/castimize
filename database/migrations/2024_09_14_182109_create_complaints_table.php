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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('complaint_reason_id')->nullable()->index();
            $table->unsignedBigInteger('upload_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->dateTime('deny_at')->nullable();
            $table->dateTime('reprint_at')->nullable();
            $table->dateTime('refund_at')->nullable();
            $table->string('reason');
            $table->tinyText('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('complaint_reason_id')->references('id')->on('complaint_reasons')->onDelete('set null');
            $table->foreign('upload_id')->references('id')->on('uploads')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });

        Schema::table('complaints', function (Blueprint $table) {
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
        Schema::dropIfExists('complaints');
    }
};
