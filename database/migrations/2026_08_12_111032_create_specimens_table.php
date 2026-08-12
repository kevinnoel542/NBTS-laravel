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
        Schema::create('specimens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_episode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collection_container_id')->nullable()->constrained()->nullOnDelete();
            $table->string('specimen_identifier', 72)->unique();
            $table->string('specimen_type', 48);
            $table->string('status', 24)->default('expected');
            $table->boolean('is_required')->default(true);
            $table->decimal('volume_ml', 5, 2)->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('handed_off_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handed_off_at')->nullable();
            $table->string('handoff_recipient')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['collection_episode_id', 'status'], 'specimen_episode_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specimens');
    }
};
