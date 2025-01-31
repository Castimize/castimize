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
        Schema::create('shop_owners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::table('shop_owners', function (Blueprint $table) {
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
        Schema::dropIfExists('shop_owners');
    }
};
