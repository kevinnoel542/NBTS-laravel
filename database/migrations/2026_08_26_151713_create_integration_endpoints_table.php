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
        Schema::create('integration_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users', indexName: 'integration_endpoint_owner_fk')->nullOnDelete();
            $table->string('system_code', 96)->unique();
            $table->string('name');
            $table->string('endpoint_type', 64);
            $table->string('standard_profile')->nullable();
            $table->string('base_url')->nullable();
            $table->text('encrypted_config')->nullable();
            $table->boolean('acknowledgement_required')->default(true);
            $table->boolean('idempotency_required')->default(true);
            $table->boolean('sequence_check_required')->default(true);
            $table->boolean('dead_letter_enabled')->default(true);
            $table->json('retry_policy');
            $table->string('status', 32)->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'endpoint_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_endpoints');
    }
};
