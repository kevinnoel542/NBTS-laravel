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
        Schema::create('collection_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_episode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collection_container_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('specimen_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label_identifier', 80)->unique();
            $table->string('symbology', 24)->default('code_128_b');
            $table->string('template_version', 48);
            $table->string('status', 24)->default('generated');
            $table->unsignedSmallInteger('print_count')->default(0);
            $table->string('printer_name')->nullable();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['collection_episode_id', 'status'], 'collection_label_episode_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_labels');
    }
};
