<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('wali_kelas')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->string('nip', 30)->nullable()->after('phone');
            $table->string('photo')->nullable()->after('nip');
            $table->boolean('is_active')->default(true)->after('photo');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'nip', 'photo', 'is_active', 'last_login_at']);
        });
    }
};
