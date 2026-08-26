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
        Schema::create('privacy_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'privacy_notice_approved_by_fk')->nullOnDelete();
            $table->string('notice_code', 96);
            $table->unsignedSmallInteger('version');
            $table->string('title');
            $table->json('channels');
            $table->json('consent_scope');
            $table->json('communication_preferences')->nullable();
            $table->string('status', 32)->default('draft');
            $table->date('effective_from')->nullable();
            $table->date('retired_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['notice_code', 'version']);
            $table->index(['status', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacy_notices');
    }
};
