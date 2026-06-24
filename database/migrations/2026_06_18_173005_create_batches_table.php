<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiration_date');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->string('supplier_name')->nullable();
            $table->string('supplier_document')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['drug_id', 'pharmacy_id', 'batch_number']);
            $table->index(['expiration_date', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
