<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_members', function (Blueprint $table) {
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu')->after('phone');
            $table->text('hrd_note')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('application_members', function (Blueprint $table) {
            $table->dropColumn(['status', 'hrd_note']);
        });
    }
};
