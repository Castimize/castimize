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
        Schema::create('model_has_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->index();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->boolean('default_billing')->default(0);
            $table->boolean('default_shipping')->default(0);
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();

            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');

            $table->index(['model_id', 'model_type']);


            $table->primary(['address_id', 'model_id', 'model_type'],
                'model_has_addresses_address_model_type_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_addresses');
    }
};
