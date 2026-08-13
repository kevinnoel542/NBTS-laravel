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
        Schema::create('hospital_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained(indexName: 'hospital_service_hospital_fk')->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('service_type', 64);
            $table->string('status', 32)->default('active');
            $table->json('contacts')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('operating_hours')->nullable();
            $table->json('request_routes')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'code'], 'hospital_service_code_unique');
            $table->index(['hospital_id', 'status', 'service_type'], 'hospital_service_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_services');
    }
};
