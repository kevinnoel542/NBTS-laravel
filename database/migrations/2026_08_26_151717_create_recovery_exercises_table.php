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
        Schema::create('recovery_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->nullable()->constrained('users', indexName: 'recovery_operator_fk')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users', indexName: 'recovery_approver_fk')->nullOnDelete();
            $table->string('exercise_reference', 96)->unique();
            $table->string('scenario');
            $table->unsignedInteger('rto_minutes');
            $table->unsignedInteger('rpo_minutes');
            $table->timestamp('recovery_point_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->json('validation_checks');
            $table->json('exceptions')->nullable();
            $table->timestamp('reopening_approved_at')->nullable();
            $table->string('capa_reference')->nullable();
            $table->string('status', 32)->default('planned');
            $table->timestamps();

            $table->index(['status', 'scenario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_exercises');
    }
};
