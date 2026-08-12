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
        Schema::create('donor_identity_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canonical_donor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('source_donor_id')->unique()->constrained('users')->restrictOnDelete();
            $table->foreignId('duplicate_case_id')->unique()->constrained('donor_duplicate_cases')->restrictOnDelete();
            $table->string('source_donor_identifier');
            $table->foreignId('merged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('merged_at');
            $table->timestamps();

            $table->index(['canonical_donor_id', 'merged_at'], 'donor_alias_canonical_merged_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_identity_aliases');
    }
};
