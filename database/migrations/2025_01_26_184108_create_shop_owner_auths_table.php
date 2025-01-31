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
        Schema::create('shop_owner_auths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_owner_id')->index();
            $table->string('shop');
            $table->json('shop_oauth');
            $table->boolean('active')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_owner_id')->references('id')->on('shop_owners')->onDelete('cascade');
        });

        Schema::table('shop_owner_auths', function (Blueprint $table) {
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
        Schema::dropIfExists('shop_owner_auths');
    }
};
