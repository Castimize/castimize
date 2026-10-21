<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_shipments', function (Blueprint $table) {
            $table->json('shippo_request_data')->nullable()->after('shippo_shipment_meta_data');
        });
    }

    public function down(): void
    {
        Schema::table('customer_shipments', function (Blueprint $table) {
            $table->dropColumn('shippo_request_data');
        });
    }
};
