<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drug_presentations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('health_insurances', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('drugs', function (Blueprint $table) {
            $table->foreignId('drug_presentation_id')->nullable()->after('category')->constrained('drug_presentations')->nullOnDelete();
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->foreignId('health_insurance_id')->nullable()->after('cost_center_id')->constrained('health_insurances')->nullOnDelete();
            $table->date('admission_date')->nullable()->after('birth_date');
            $table->text('allergies')->nullable()->after('room_number');
            $table->text('rescue_service')->nullable()->after('allergies');
            $table->text('diagnosis')->nullable()->after('rescue_service');
        });

        Schema::create('resident_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained()->restrictOnDelete();
            $table->foreignId('drug_presentation_id')->nullable()->constrained('drug_presentations')->nullOnDelete();
            $table->decimal('daily_dose', 10, 2)->default(0);
            $table->decimal('monthly_dose', 10, 2)->default(0);
            $table->time('schedule_time')->nullable();
            $table->text('observations')->nullable();
            $table->string('treatment_type')->default('cronico');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['resident_id', 'is_active']);
            $table->index(['drug_id', 'treatment_type']);
        });

        Schema::create('resident_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->timestamp('accessed_at');
            $table->timestamps();

            $table->index(['resident_id', 'accessed_at']);
            $table->index(['user_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_access_logs');
        Schema::dropIfExists('resident_treatments');

        Schema::table('residents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('health_insurance_id');
            $table->dropColumn(['admission_date', 'allergies', 'rescue_service', 'diagnosis']);
        });

        Schema::table('drugs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('drug_presentation_id');
        });

        Schema::dropIfExists('health_insurances');
        Schema::dropIfExists('drug_presentations');
    }
};
