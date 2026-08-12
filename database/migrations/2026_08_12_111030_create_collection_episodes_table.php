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
        Schema::create('collection_episodes', function (Blueprint $table) {
            $table->id();
            $table->string('donation_identifier', 64)->unique();
            $table->foreignId('donor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('blood_center_id')->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('identity_check_id')->constrained('donor_identity_checks')->restrictOnDelete();
            $table->foreignId('eligibility_record_id')->constrained()->restrictOnDelete();
            $table->foreignId('donation_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('status', 24)->default('prepared');
            $table->string('outcome', 24)->nullable();
            $table->string('donation_method', 32)->default('whole_blood');
            $table->string('bag_type', 64);
            $table->string('bag_lot', 96);
            $table->string('device_identifier', 96)->nullable();
            $table->unsignedSmallInteger('planned_volume_ml')->default(450);
            $table->unsignedSmallInteger('actual_volume_ml')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_mode', 16)->default('online');
            $table->timestamp('aftercare_confirmed_at')->nullable();
            $table->timestamp('donor_acknowledged_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['blood_center_id', 'status', 'created_at'], 'collection_episode_center_status_index');
            $table->index(['donor_id', 'created_at'], 'collection_episode_donor_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_episodes');
    }
};
