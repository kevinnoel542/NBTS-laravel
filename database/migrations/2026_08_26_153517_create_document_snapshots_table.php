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
        Schema::create('document_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users', indexName: 'document_snapshot_generated_by_fk')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'document_snapshot_approved_by_fk')->nullOnDelete();
            $table->string('document_reference', 64)->unique();
            $table->string('document_type', 80);
            $table->string('locale', 8)->default('en');
            $table->date('source_period_start')->nullable();
            $table->date('source_period_end')->nullable();
            $table->json('stable_identifiers');
            $table->json('labels');
            $table->json('access_scope');
            $table->json('verification_context');
            $table->text('encrypted_snapshot_payload');
            $table->string('checksum', 128);
            $table->boolean('authorized')->default(false);
            $table->boolean('audited')->default(false);
            $table->boolean('large_export')->default(false);
            $table->boolean('queued')->default(false);
            $table->string('queue_name')->nullable();
            $table->string('status', 32)->default('queued');
            $table->timestamp('generated_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'status']);
            $table->index(['large_export', 'queued', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_snapshots');
    }
};
