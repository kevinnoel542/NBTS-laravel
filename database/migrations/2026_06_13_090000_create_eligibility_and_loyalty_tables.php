<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->enum('eligibility_status', ['eligible', 'not_yet_eligible', 'temporarily_deferred', 'permanently_deferred'])->default('eligible')->after('next_eligible_donation_date');
            $table->timestamp('last_eligibility_checked_at')->nullable()->after('eligibility_status');
            $table->text('eligibility_notes')->nullable()->after('last_eligibility_checked_at');
        });

        Schema::create('eligibility_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['eligible', 'not_yet_eligible', 'temporarily_deferred', 'permanently_deferred']);
            $table->unsignedTinyInteger('age')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->json('answers')->nullable();
            $table->date('next_eligible_donation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('deferrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['temporary', 'permanent']);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('lifted_at')->nullable();
            $table->foreignId('lifted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('donation_threshold')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('donor_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('donation_threshold')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('donor_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['earned', 'redeemed'])->default('earned');
            $table->timestamp('awarded_at');
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'reward_id']);
        });

        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period')->default('all_time');
            $table->unsignedInteger('donation_count')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
        Schema::dropIfExists('donor_rewards');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('donor_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('deferrals');
        Schema::dropIfExists('eligibility_records');

        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->dropColumn(['eligibility_status', 'last_eligibility_checked_at', 'eligibility_notes']);
        });
    }
};
