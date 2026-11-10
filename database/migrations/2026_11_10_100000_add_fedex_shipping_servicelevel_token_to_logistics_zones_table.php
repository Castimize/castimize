<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logistics_zones', function (Blueprint $table) {
            $table->string('fedex_shipping_servicelevel_token')->nullable()->after('shipping_servicelevel_token');
        });
    }

    public function down(): void
    {
        Schema::table('logistics_zones', function (Blueprint $table) {
            $table->dropColumn('fedex_shipping_servicelevel_token');
        });
    }
};
