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
        Schema::create('rejections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id')->index();
            $table->unsignedBigInteger('order_queue_id')->index();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('upload_id')->index();
            $table->unsignedBigInteger('rejection_reason_id')->nullable()->index();
            $table->string('reason_manufacturer');
            $table->tinyText('note_manufacturer')->nullable();
            $table->tinyText('note_castimize')->nullable();
            $table->string('photo');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('cascade');
            $table->foreign('order_queue_id')->references('id')->on('order_queue')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('upload_id')->references('id')->on('uploads')->onDelete('cascade');
            $table->foreign('rejection_reason_id')->references('id')->on('rejection_reasons')->onDelete('set null');
        });

        Schema::table('rejections', function (Blueprint $table) {
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
        Schema::dropIfExists('rejections');
    }
};
