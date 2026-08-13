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
        Schema::create('hospital_component_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_blood_request_id')->constrained(indexName: 'hca_request_fk')->cascadeOnDelete();
            $table->foreignId('blood_component_id')->constrained(indexName: 'hca_component_fk')->restrictOnDelete();
            $table->foreignId('compatibility_test_id')->nullable()->constrained(indexName: 'hca_compat_test_fk')->nullOnDelete();
            $table->foreignId('emergency_release_authorization_id')->nullable()->constrained(indexName: 'hca_emergency_release_fk')->nullOnDelete();
            $table->foreignId('allocated_by')->constrained('users', indexName: 'hca_allocated_by_fk')->restrictOnDelete();
            $table->foreignId('issue_checked_by')->nullable()->constrained('users', indexName: 'hca_issue_checked_by_fk')->nullOnDelete();
            $table->string('status', 32)->default('allocated');
            $table->timestamp('allocated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->json('final_check')->nullable();
            $table->string('issue_reference', 96)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['hospital_blood_request_id', 'blood_component_id'], 'hca_request_component_unique');
            $table->index(['blood_component_id', 'status'], 'hca_component_status_index');
            $table->index(['hospital_blood_request_id', 'status'], 'hca_request_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_component_allocations');
    }
};
