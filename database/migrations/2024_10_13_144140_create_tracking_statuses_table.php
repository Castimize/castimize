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
        Schema::create('tracking_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('object_id')->index();
            $table->string('status');
            $table->string('sub_status')->nullable();
            $table->string('status_details', 500)->nullable();
            $table->datetime('status_date')->nullable();
            $table->json('location')->nullable();
            $table->json('meta_data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['model_id', 'model_type']);
        });

        Schema::table('tracking_statuses', function (Blueprint $table) {
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
        Schema::dropIfExists('tracking_statuses');
    }
};
