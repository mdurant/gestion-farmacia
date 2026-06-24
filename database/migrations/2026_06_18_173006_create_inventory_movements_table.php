<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_type');
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_pharmacy_id')->nullable()->constrained('pharmacies')->nullOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cost_center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('prescription_id')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_value', 14, 2)->default(0);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('movement_at');
            $table->timestamps();

            $table->index(['movement_type', 'movement_at']);
            $table->index(['drug_id', 'movement_at']);
            $table->index(['pharmacy_id', 'movement_at']);
            $table->index(['resident_id', 'movement_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
