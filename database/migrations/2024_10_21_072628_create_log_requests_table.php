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
        Schema::create('log_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('incoming');
            $table->string('path_info')->nullable()->index();
            $table->string('request_uri')->nullable();
            $table->string('method')->nullable()->index();
            $table->string('remote_address')->nullable();
            $table->string('user_agent')->nullable()->index();
            $table->json('server')->nullable();
            $table->json('headers')->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->integer('http_code')->default(200)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('log_requests', function (Blueprint $table) {
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
        Schema::dropIfExists('log_requests');
    }
};
