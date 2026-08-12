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
        Schema::create('staff_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('organization_unit_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('code', 96);
            $table->string('name');
            $table->string('status', 32)->default('active')->index();
            $table->date('valid_from')->nullable();
            $table->date('expires_at')->nullable()->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'organization_unit_id', 'code'], 'staff_competencies_scope_unique');
            $table->index(['user_id', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_competencies');
    }
};
