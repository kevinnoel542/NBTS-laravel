<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blood_center_id')->constrained()->cascadeOnDelete();
            $table->enum('position', ['center_manager', 'center_staff'])->default('center_staff');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'blood_center_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_staff');
    }
};
