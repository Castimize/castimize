<?php

use App\Enums\Admin\CurrencyEnum;
use App\Enums\Admin\PaymentFeeTypesEnum;
use App\Enums\Admin\PaymentMethodsEnum;
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
        Schema::create('payment_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->enum('payment_method', array_column(PaymentMethodsEnum::cases(), 'value'));
            $table->enum('type', array_column(PaymentFeeTypesEnum::cases(), 'value'));
            $table->double('fee');
            $table->double('minimum_fee')->nullable();
            $table->double('maximum_fee')->nullable();
            $table->string('currency_code')->default(CurrencyEnum::USD->value);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('payment_fees', function (Blueprint $table) {
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
        Schema::dropIfExists('payment_fees');
    }
};
