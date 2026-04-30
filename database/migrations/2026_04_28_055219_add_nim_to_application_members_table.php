<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('application_members', function (Blueprint $table) {
            $table->string('nim')->nullable()->after('name');
            $table->string('program_studi')->nullable()->after('major');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_members', function (Blueprint $table) {
            $table->dropColumn(['nim', 'program_studi']);
        });
    }
};
