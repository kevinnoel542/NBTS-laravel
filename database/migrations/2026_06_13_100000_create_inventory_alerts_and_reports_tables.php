<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number')->unique();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blood_center_id')->constrained()->cascadeOnDelete();
            $table->string('blood_group');
            $table->date('collection_date');
            $table->date('expiry_date');
            $table->enum('status', ['collected', 'testing', 'available', 'reserved', 'transferred', 'used', 'rejected', 'expired', 'discarded'])->default('collected');
            $table->string('current_location')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('blood_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->constrained()->cascadeOnDelete();
            $table->string('blood_group');
            $table->unsignedInteger('available_units')->default(0);
            $table->unsignedInteger('reserved_units')->default(0);
            $table->unsignedInteger('minimum_threshold')->default(5);
            $table->timestamps();

            $table->unique(['blood_center_id', 'blood_group']);
        });

        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blood_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('blood_group');
            $table->integer('quantity_delta');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('low_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->constrained()->cascadeOnDelete();
            $table->string('blood_group');
            $table->unsignedInteger('available_units');
            $table->unsignedInteger('minimum_threshold');
            $table->enum('status', ['open', 'notified', 'campaign_created', 'resolved'])->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->enum('campaign_type', ['standard', 'emergency'])->default('standard')->after('status');
            $table->string('target_blood_group')->nullable()->after('campaign_type');
            $table->foreignId('low_stock_alert_id')->nullable()->after('target_blood_group')->constrained('low_stock_alerts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['low_stock_alert_id']);
            $table->dropColumn(['campaign_type', 'target_blood_group', 'low_stock_alert_id']);
        });

        Schema::dropIfExists('low_stock_alerts');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('blood_inventory');
        Schema::dropIfExists('blood_units');
    }
};
