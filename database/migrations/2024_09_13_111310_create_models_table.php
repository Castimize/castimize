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
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('material_id')->nullable()->index();
            $table->string('model_name')->nullable()->index();
            $table->string('name')->index();
            $table->string('file_name')->index();
            $table->string('thumb_name')->nullable();
            $table->float('model_volume_cc');
            $table->float('model_x_length');
            $table->float('model_y_length');
            $table->float('model_z_length');
            $table->float('model_surface_area_cm2');
            $table->integer('model_parts');
            $table->float('model_box_volume');
            $table->float('model_scale')->default(1);
            $table->json('categories')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('set null');

            $table->index([
                'model_volume_cc',
                'model_x_length',
                'model_y_length',
                'model_z_length',
                'model_surface_area_cm2',
                'model_parts',
                'model_box_volume',
            ], 'models_stats_index');
        });

        Schema::table('models', function (Blueprint $table) {
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
        Schema::dropIfExists('models');
    }
};
