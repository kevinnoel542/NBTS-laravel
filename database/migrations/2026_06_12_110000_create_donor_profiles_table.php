<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('donor_id')->unique();
            $table->enum('blood_group_status', ['unknown', 'user_selected', 'staff_verified'])->default('unknown');
            $table->boolean('blood_group_verified')->default(false);
            $table->timestamp('blood_group_verified_at')->nullable();
            $table->foreignId('blood_group_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('next_eligible_donation_date')->nullable();
            $table->unsignedInteger('total_donations')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_profiles');
    }
};
