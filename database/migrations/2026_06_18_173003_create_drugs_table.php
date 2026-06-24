<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('presentation')->nullable();
            $table->string('active_ingredient')->nullable();
            $table->boolean('is_controlled')->default(false);
            $table->boolean('is_narcotic')->default(false);
            $table->unsignedInteger('min_stock')->default(0);
            $table->unsignedInteger('max_stock')->nullable();
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
