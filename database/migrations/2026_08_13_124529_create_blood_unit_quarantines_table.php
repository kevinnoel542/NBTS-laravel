<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_unit_quarantines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_unit_id')->unique()->constrained(indexName: 'blood_unit_quarantine_unit_fk')->cascadeOnDelete();
            $table->string('status', 24)->default('held');
            $table->json('reasons');
            $table->timestamp('held_at')->useCurrent();
            $table->foreignId('held_by')->nullable()->constrained('users', indexName: 'blood_unit_quarantine_held_by_fk')->nullOnDelete();
            $table->timestamp('release_criteria_completed_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users', indexName: 'blood_unit_quarantine_released_by_fk')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'release_criteria_completed_at'], 'blood_unit_quarantine_release_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_unit_quarantines');
    }
};
