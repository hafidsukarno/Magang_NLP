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
        Schema::table('applications', function (Blueprint $table) {
            $table->string('surat_laporan_path')->nullable()->after('surat_permohonan_type');
            $table->longText('surat_laporan_extracted_text')->nullable()->after('surat_laporan_path');
            $table->string('surat_laporan_title')->nullable()->after('surat_laporan_extracted_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['surat_laporan_path', 'surat_laporan_extracted_text', 'surat_laporan_title']);
        });
    }
};
