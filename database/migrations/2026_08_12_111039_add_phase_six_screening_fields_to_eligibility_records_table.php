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
        Schema::table('eligibility_records', function (Blueprint $table) {
            $table->foreignId('blood_center_id')->nullable()->after('checked_by')->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->after('blood_center_id')->constrained()->nullOnDelete();
            $table->foreignId('identity_check_id')->nullable()->after('appointment_id')->constrained('donor_identity_checks')->nullOnDelete();
            $table->foreignId('screening_protocol_id')->nullable()->after('identity_check_id')->constrained()->nullOnDelete();
            $table->string('questionnaire_version', 64)->nullable()->after('screening_protocol_id');
            $table->string('rule_version', 64)->nullable()->after('questionnaire_version');
            $table->decimal('hemoglobin_g_dl', 4, 2)->nullable()->after('weight_kg');
            $table->json('observations')->nullable()->after('answers');
            $table->string('decision_code', 64)->nullable()->after('observations');
            $table->string('source_mode', 16)->default('online')->after('decision_code');
            $table->boolean('self_excluded')->default(false)->after('source_mode');
            $table->text('counselling_notes')->nullable()->after('self_excluded');
            $table->text('referral')->nullable()->after('counselling_notes');
            $table->date('reentry_date')->nullable()->after('referral');
            $table->text('override_reason')->nullable()->after('reentry_date');
            $table->timestamp('screened_at')->nullable()->after('override_reason');

            $table->index(['blood_center_id', 'status', 'screened_at'], 'eligibility_center_status_screened_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eligibility_records', function (Blueprint $table) {
            $table->dropForeign(['blood_center_id']);
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['identity_check_id']);
            $table->dropForeign(['screening_protocol_id']);
            $table->dropIndex('eligibility_center_status_screened_index');
            $table->dropColumn([
                'blood_center_id',
                'appointment_id',
                'identity_check_id',
                'screening_protocol_id',
                'questionnaire_version',
                'rule_version',
                'hemoglobin_g_dl',
                'observations',
                'decision_code',
                'source_mode',
                'self_excluded',
                'counselling_notes',
                'referral',
                'reentry_date',
                'override_reason',
                'screened_at',
            ]);
        });
    }
};
