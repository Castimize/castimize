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
        Schema::create('material_model', function (Blueprint $table) {
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();

            $table->primary(['material_id', 'model_id']);

            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            $table->foreign('model_id')->references('id')->on('models')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_model');
    }
};
