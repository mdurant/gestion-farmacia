<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_token', 64)->nullable()->index();
            $table->string('browser', 120)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('location', 255)->nullable();
            $table->timestamp('connected_at');
            $table->timestamp('disconnected_at')->nullable();
            $table->string('disconnect_reason', 60)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'connected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_access_logs');
    }
};
