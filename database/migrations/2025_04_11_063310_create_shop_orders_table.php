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
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_owner_id');
            $table->unsignedBigInteger('shop_id');
            $table->string('order_number')->index();
            $table->unsignedBigInteger('shop_receipt_id');
            $table->string('state')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_owner_id')->references('id')->on('shop_owners');
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->foreign('model_id')->references('id')->on('models');

            $table->index(['shop_owner_id', 'shop_id', 'order_number'], 'so_id_s_id_o_num_idx');
        });

        Schema::table('shop_orders', function (Blueprint $table) {
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
        Schema::dropIfExists('shop_orders');
    }
};
