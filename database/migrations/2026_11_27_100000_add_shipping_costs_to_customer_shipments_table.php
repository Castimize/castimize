<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_costs')->nullable()->after('service_costs');
            $table->string('shipping_costs_currency_code', 3)->nullable()->after('shipping_costs');
        });

        DB::table('customer_shipments')
            ->whereNotNull('shippo_shipment_meta_data')
            ->whereNotNull('shippo_transaction_meta_data')
            ->orderBy('id')
            ->each(function (object $row): void {
                $shipment = json_decode($row->shippo_shipment_meta_data, true);
                $transaction = json_decode($row->shippo_transaction_meta_data, true);
                $rateId = is_array($transaction) ? ($transaction['rate'] ?? null) : null;

                foreach (is_array($shipment) ? ($shipment['rates'] ?? []) : [] as $rate) {
                    if ($rateId !== null && ($rate['object_id'] ?? null) === $rateId) {
                        DB::table('customer_shipments')->where('id', $row->id)->update([
                            'shipping_costs' => (int) round((float) $rate['amount'] * 100),
                            'shipping_costs_currency_code' => $rate['currency'] ?? null,
                        ]);
                        break;
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('customer_shipments', function (Blueprint $table) {
            $table->dropColumn(['shipping_costs', 'shipping_costs_currency_code']);
        });
    }
};
