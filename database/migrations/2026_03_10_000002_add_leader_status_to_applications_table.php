<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->enum('leader_status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu')->after('status');
            $table->text('leader_note')->nullable()->after('leader_status');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['leader_status', 'leader_note']);
        });
    }
};
