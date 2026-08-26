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
        Schema::create('data_processing_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users', indexName: 'dpi_owner_fk')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'dpi_approved_by_fk')->nullOnDelete();
            $table->string('process_code', 96)->unique();
            $table->string('name');
            $table->json('data_subjects');
            $table->json('data_categories');
            $table->json('purposes');
            $table->string('lawful_basis');
            $table->string('controller');
            $table->json('processors')->nullable();
            $table->json('minimization_controls')->nullable();
            $table->json('vendor_controls')->nullable();
            $table->boolean('dpia_required')->default(false);
            $table->string('dpia_reference')->nullable();
            $table->text('breach_response_playbook')->nullable();
            $table->json('rights_handling')->nullable();
            $table->string('status', 32)->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('review_due_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'review_due_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_processing_inventories');
    }
};
