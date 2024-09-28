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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->string('name');
            $table->float('fee');
            $table->string('currency_code')->default('USD');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('services', function (Blueprint $table) {
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
        Schema::dropIfExists('services');
    }
};
