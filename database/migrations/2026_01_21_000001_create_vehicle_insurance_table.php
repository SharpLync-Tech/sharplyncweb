<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sharpfleet')->create('vehicle_insurance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('vehicle_id');
            $table->string('insurance_company', 150)->nullable();
            $table->string('policy_number', 100)->nullable();
            $table->string('policy_type', 50)->nullable();
            $table->string('policy_document_path')->nullable();
            $table->string('policy_document_original_name')->nullable();
            $table->string('policy_document_mime', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('notify_email')->nullable();
            $table->unsignedInteger('notify_window_days')->nullable();
            $table->timestamps();

            $table->unique('vehicle_id');
            $table->index('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::connection('sharpfleet')->dropIfExists('vehicle_insurance');
    }
};
