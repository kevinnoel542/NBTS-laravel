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
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->string('privacy_notice_version', 64)->nullable()->after('language');
            $table->timestamp('consented_at')->nullable()->after('privacy_notice_version');
            $table->foreignId('consent_recorded_by')
                ->nullable()
                ->after('consented_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('consent_source', 32)->nullable()->after('consent_recorded_by');
            $table->boolean('identity_review_required')->default(false)->after('consent_source')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donor_profiles', function (Blueprint $table) {
            $table->dropForeign(['consent_recorded_by']);
            $table->dropColumn([
                'privacy_notice_version',
                'consented_at',
                'consent_recorded_by',
                'consent_source',
                'identity_review_required',
            ]);
        });
    }
};
