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
        Schema::create('protected_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users', indexName: 'protected_export_requested_by_fk')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'protected_export_approved_by_fk')->nullOnDelete();
            $table->string('export_reference', 96)->unique();
            $table->string('purpose');
            $table->string('recipient');
            $table->json('scope');
            $table->string('delivery_channel', 64);
            $table->text('encrypted_manifest')->nullable();
            $table->string('status', 32)->default('requested');
            $table->timestamp('expires_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protected_exports');
    }
};
