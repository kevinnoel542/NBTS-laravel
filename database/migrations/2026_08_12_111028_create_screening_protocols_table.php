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
        Schema::create('screening_protocols', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64);
            $table->unsignedSmallInteger('version');
            $table->string('title');
            $table->string('status', 24)->default('draft');
            $table->json('questionnaire');
            $table->json('rules');
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_construction_only')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['code', 'version']);
            $table->index(['status', 'effective_from', 'effective_until'], 'screening_protocol_effective_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_protocols');
    }
};
