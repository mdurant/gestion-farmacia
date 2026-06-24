<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->text('rut');
            $table->text('first_name');
            $table->text('last_name');
            $table->date('birth_date')->nullable();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->text('room_number')->nullable();
            $table->text('emergency_contact_name')->nullable();
            $table->text('emergency_contact_phone')->nullable();
            $table->text('medical_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'cost_center_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
