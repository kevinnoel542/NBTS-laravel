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
        Schema::create('collection_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_episode_id')->constrained()->cascadeOnDelete();
            $table->string('container_identifier', 72)->unique();
            $table->string('kind', 32)->default('primary');
            $table->string('manufacturer_lot', 96);
            $table->string('status', 24)->default('quarantined');
            $table->string('quarantine_location')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('quarantined_at')->nullable();
            $table->timestamps();

            $table->index(['collection_episode_id', 'status'], 'collection_container_episode_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_containers');
    }
};
