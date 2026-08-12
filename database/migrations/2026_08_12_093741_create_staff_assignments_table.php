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
        Schema::create('staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('organization_unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('work_location_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('shift', 64)->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->string('status', 32)->default('active')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->foreignId('revoked_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'starts_at', 'ends_at'], 'staff_assignments_user_effective_index');
            $table->index(['organization_unit_id', 'department_id', 'status'], 'staff_assignments_scope_index');
            $table->index(['role_id', 'organization_unit_id', 'status'], 'staff_assignments_role_scope_index');
            $table->unique(
                ['user_id', 'role_id', 'organization_unit_id', 'department_id', 'starts_at'],
                'staff_assignments_identity_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_assignments');
    }
};
