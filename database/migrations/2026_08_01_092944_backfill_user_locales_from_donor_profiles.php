<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE users
            INNER JOIN donor_profiles ON donor_profiles.user_id = users.id
            SET users.locale = donor_profiles.language
            WHERE donor_profiles.language IN ('en', 'sw')
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Existing locale preferences cannot be safely inferred after rollback.
    }
};
