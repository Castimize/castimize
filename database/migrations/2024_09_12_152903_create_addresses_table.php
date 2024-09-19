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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('place_id')->nullable()->index();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('house_number', 100);
            $table->string('postal_code');
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->string('administrative_area')->nullable();
            $table->unsignedBigInteger('state_id')->nullable()->index();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

            $table->index(['lat', 'lng']);
            $table->index(['postal_code', 'house_number']);
        });

        Schema::table('addresses', function (Blueprint $table) {
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
        Schema::dropIfExists('addresses');
    }
};
