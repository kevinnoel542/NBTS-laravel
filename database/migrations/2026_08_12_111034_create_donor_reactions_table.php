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
        Schema::create('donor_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_episode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('blood_center_id')->constrained()->restrictOnDelete();
            $table->string('severity', 24);
            $table->string('reaction_type', 64);
            $table->json('symptoms');
            $table->timestamp('occurred_at');
            $table->text('treatment')->nullable();
            $table->text('referral')->nullable();
            $table->text('outcome')->nullable();
            $table->boolean('followup_required')->default(false);
            $table->timestamp('followup_due_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['blood_center_id', 'severity', 'occurred_at'], 'donor_reaction_center_severity_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_reactions');
    }
};
