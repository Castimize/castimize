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
        Schema::create('reprints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_queue_id')->index();
            $table->unsignedBigInteger('reprint_culprit_id')->index();
            $table->unsignedBigInteger('reprint_reason_id')->nullable()->index();
            $table->string('reason');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_queue_id')->references('id')->on('order_queue')->onDelete('cascade');
            $table->foreign('reprint_culprit_id')->references('id')->on('reprint_culprits')->onDelete('cascade');
            $table->foreign('reprint_reason_id')->references('id')->on('reprint_reasons')->onDelete('set null');
        });

        Schema::table('reprints', function (Blueprint $table) {
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
        Schema::dropIfExists('reprints');
    }
};
