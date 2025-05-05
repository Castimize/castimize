<?php

use App\Enums\Etsy\EtsyListingStatesEnum;
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
            $table->id();
            $table->unsignedBigInteger('shop_owner_id');
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('taxonomy_id')->nullable();
            $table->unsignedBigInteger('shop_listing_id');
            $table->unsignedBigInteger('shop_listing_image_id')->nullable();
            $table->string('state')->default(EtsyListingStatesEnum::Draft->value);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_owner_id')->references('id')->on('shop_owners');
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->foreign('model_id')->references('id')->on('models');

            $table->index(['shop_owner_id', 'shop_id', 'model_id'], 'so_id_soa_id_m_id_idx');
        });

        Schema::table('shop_listing_models', function (Blueprint $table) {
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
        Schema::dropIfExists('shop_listing_models');
    }
};
