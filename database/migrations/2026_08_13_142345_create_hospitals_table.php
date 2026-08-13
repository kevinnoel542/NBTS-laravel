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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->nullable()->constrained(indexName: 'hospital_org_unit_fk')->nullOnDelete();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('status', 32)->default('active');
            $table->string('blood_bank_level', 64)->nullable();
            $table->json('contacts')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('operating_hours')->nullable();
            $table->json('request_routes')->nullable();
            $table->string('integration_identifier', 120)->nullable()->unique();
            $table->json('minimum_patient_identity_fields')->nullable();
            $table->string('privacy_policy_version', 64)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'hospital_approved_by_fk')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'blood_bank_level'], 'hospital_status_level_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
