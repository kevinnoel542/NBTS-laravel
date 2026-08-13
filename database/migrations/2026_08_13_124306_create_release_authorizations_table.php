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
        Schema::create('release_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_unit_id')->constrained(indexName: 'release_auth_unit_fk')->cascadeOnDelete();
            $table->string('criteria_version', 32);
            $table->string('decision', 32);
            $table->json('evaluated_tests');
            $table->json('exceptions')->nullable();
            $table->foreignId('approved_by')->constrained('users', indexName: 'release_auth_approved_by_fk')->restrictOnDelete();
            $table->foreignId('independent_approved_by')->nullable()->constrained('users', indexName: 'release_auth_independent_by_fk')->restrictOnDelete();
            $table->foreignId('released_by')->nullable()->constrained('users', indexName: 'release_auth_released_by_fk')->restrictOnDelete();
            $table->timestamp('authorized_at');
            $table->string('reason', 500);
            $table->boolean('electronic_signature')->default(false);
            $table->timestamps();

            $table->index(['blood_unit_id', 'decision'], 'release_auth_unit_decision_index');
            $table->index(['criteria_version', 'authorized_at'], 'release_auth_criteria_time_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_authorizations');
    }
};
