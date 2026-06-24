<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_access_logs', function (Blueprint $table) {
            $table->dropForeign(['resident_id']);
        });

        Schema::table('resident_access_logs', function (Blueprint $table) {
            $table->foreignId('resident_id')->nullable()->change();
            $table->foreign('resident_id')->references('id')->on('residents')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('resident_access_logs', function (Blueprint $table) {
            $table->dropForeign(['resident_id']);
        });

        Schema::table('resident_access_logs', function (Blueprint $table) {
            $table->foreignId('resident_id')->nullable(false)->change();
            $table->foreign('resident_id')->references('id')->on('residents')->cascadeOnDelete();
        });
    }
};
