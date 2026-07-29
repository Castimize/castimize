<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_shipments', function (Blueprint $table) {
            $table->string('carrier')->nullable()->after('currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('customer_shipments', function (Blueprint $table) {
            $table->dropColumn('carrier');
        });
    }
};
