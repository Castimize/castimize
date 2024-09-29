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
        Schema::create('order_queue_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_queue_id')->index();
            $table->unsignedBigInteger('order_status_id')->nullable()->index();
            $table->string('status');
            $table->string('slug')->index();
            $table->dateTime('target_date')->index()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_queue_id')->references('id')->on('order_queue')->onDelete('cascade');
            $table->foreign('order_status_id')->references('id')->on('order_statuses')->onDelete('set null');
        });

        Schema::table('order_queue_statuses', function (Blueprint $table) {
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
        Schema::dropIfExists('order_queue_statuses');
    }
};
