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
        Schema::create('backup_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->nullable()->constrained('users', indexName: 'backup_run_operator_fk')->nullOnDelete();
            $table->string('backup_reference', 96)->unique();
            $table->string('backup_type', 64);
            $table->string('storage_location');
            $table->boolean('encrypted')->default(false);
            $table->boolean('offsite')->default(false);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 128)->nullable();
            $table->string('status', 32)->default('started');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('restore_tested_at')->nullable();
            $table->timestamp('retention_until')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'started_at']);
            $table->index(['verified_at', 'restore_tested_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_runs');
    }
};
