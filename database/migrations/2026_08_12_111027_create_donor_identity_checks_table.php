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
        Schema::create('donor_identity_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('blood_center_id')->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method', 32);
            $table->string('reference_suffix', 12)->nullable();
            $table->string('status', 24);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('source_mode', 16)->default('online');
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(
                ['donor_id', 'blood_center_id', 'status', 'expires_at'],
                'donor_identity_effective_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_identity_checks');
    }
};
