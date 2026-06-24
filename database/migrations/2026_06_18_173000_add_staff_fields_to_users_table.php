<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('first_name')->nullable()->after('name');
            $table->text('last_name')->nullable()->after('first_name');
            $table->text('rut')->nullable()->after('last_name');
            $table->string('role')->default('tens')->after('rut');
            $table->boolean('is_active')->default(true)->after('role');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['first_name', 'last_name', 'rut', 'role', 'is_active']);
        });
    }
};
