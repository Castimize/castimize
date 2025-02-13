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
        Schema::create('shop_listing_models', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_owner_id');
            $table->unsignedBigInteger('shop_owner_auth_id');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('shop_listing_id');
            $table->unsignedBigInteger('shop_listing_image_id')->nullable();
            $table->string('state')->default('draft');
            $table->timestamps();

            $table->foreign('shop_owner_id')->references('id')->on('shop_owners');
            $table->foreign('shop_owner_auth_id')->references('id')->on('shop_owner_auths');
            $table->foreign('model_id')->references('id')->on('models');

            $table->index(['shop_owner_id', 'shop_owner_auth_id', 'model_id']);

            $table->primary(['shop_owner_auth_id', 'model_id'],
                'shop_listing_model_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_listing_models');
    }
};
