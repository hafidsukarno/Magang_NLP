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
            // File path untuk surat permohonan
            $table->string('surat_permohonan_path')->nullable()->after('file_path');
            
            // Hasil OCR
            $table->text('surat_permohonan_extracted_text')->nullable()->after('surat_permohonan_path');
            $table->string('surat_permohonan_nama')->nullable()->after('surat_permohonan_extracted_text');
            $table->string('surat_permohonan_major')->nullable()->after('surat_permohonan_nama');
            $table->enum('surat_permohonan_type', ['individual', 'group'])->nullable()->after('surat_permohonan_major');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'surat_permohonan_path',
                'surat_permohonan_extracted_text',
                'surat_permohonan_nama',
                'surat_permohonan_major',
                'surat_permohonan_type'
            ]);
        });
    }
};
