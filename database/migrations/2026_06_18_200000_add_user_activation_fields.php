<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('activated_at')->nullable()->after('is_active');
        });

        Schema::create('user_activation_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'consumed_at']);
        });

        \Illuminate\Support\Facades\DB::table('users')
            ->where('is_active', true)
            ->whereNull('activated_at')
            ->update(['activated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activation_challenges');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('activated_at');
        });
    }
};
