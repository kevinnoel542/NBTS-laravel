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
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'retention_approved_by_fk')->nullOnDelete();
            $table->string('record_category', 96)->unique();
            $table->unsignedInteger('retention_period_days');
            $table->unsignedInteger('archival_after_days')->nullable();
            $table->string('legal_basis');
            $table->json('secure_archive_controls')->nullable();
            $table->boolean('deletion_restricted')->default(true);
            $table->string('status', 32)->default('draft');
            $table->date('effective_from')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'deletion_restricted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retention_policies');
    }
};
