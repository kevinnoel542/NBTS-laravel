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
        Schema::create('quality_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'qdoc_approved_by_fk')->nullOnDelete();
            $table->string('document_code', 96);
            $table->unsignedSmallInteger('version');
            $table->string('title');
            $table->string('document_type', 64);
            $table->string('status', 32)->default('draft');
            $table->json('applies_to_workflows')->nullable();
            $table->text('summary')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('retired_at')->nullable();
            $table->timestamps();

            $table->unique(['document_code', 'version'], 'qdoc_code_version_unique');
            $table->index(['document_type', 'status'], 'qdoc_type_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_documents');
    }
};
