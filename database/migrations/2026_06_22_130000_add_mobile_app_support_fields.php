<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->foreignId('preferred_center_id')->nullable()->after('user_id')->constrained('blood_centers')->nullOnDelete();
            $table->unsignedInteger('loyalty_points')->default(0)->after('total_donations');
            $table->string('loyalty_tier')->default('Pending')->after('loyalty_points');
            $table->string('emergency_contact_name')->nullable()->after('eligibility_notes');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->boolean('push_notifications_enabled')->default(true)->after('emergency_contact_phone');
            $table->boolean('sms_reminders_enabled')->default(true)->after('push_notifications_enabled');
            $table->boolean('share_anonymized_data')->default(false)->after('sms_reminders_enabled');
            $table->string('language')->default('English')->after('share_anonymized_data');
        });

        Schema::table('blood_centers', function (Blueprint $table) {
            $table->string('opening_hours')->nullable()->after('email');
            $table->json('services')->nullable()->after('opening_hours');
            $table->string('capacity_label')->nullable()->after('services');
            $table->unsignedSmallInteger('estimated_wait_minutes')->nullable()->after('capacity_label');
            $table->string('center_type')->nullable()->after('estimated_wait_minutes');
            $table->string('image_path')->nullable()->after('center_type');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('rescheduled_at')->nullable()->after('cancelled_at');
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('image_path')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('rescheduled_at');
        });

        Schema::table('blood_centers', function (Blueprint $table) {
            $table->dropColumn([
                'opening_hours',
                'services',
                'capacity_label',
                'estimated_wait_minutes',
                'center_type',
                'image_path',
            ]);
        });

        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->dropForeign(['preferred_center_id']);
            $table->dropColumn([
                'preferred_center_id',
                'loyalty_points',
                'loyalty_tier',
                'emergency_contact_name',
                'emergency_contact_phone',
                'push_notifications_enabled',
                'sms_reminders_enabled',
                'share_anonymized_data',
                'language',
            ]);
        });
    }
};
