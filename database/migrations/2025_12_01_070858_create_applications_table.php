<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('registration_code')->unique();
            $table->string('type')->default('individual');
            $table->string('leader_name')->nullable();
            $table->string('leader_nim')->nullable();
            $table->string('leader_email')->nullable();
            $table->string('leader_phone')->nullable();
            $table->string('university')->nullable();
            $table->string('major')->nullable();
            $table->string('program_studi')->nullable();
            $table->text('keahlian')->nullable();
            $table->text('keahlian_raw_text')->nullable();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->string('surat_permohonan_path')->nullable();
            $table->text('surat_permohonan_extracted_text')->nullable();
            
            $table->string('surat_laporan_path')->nullable();
            $table->text('surat_laporan_extracted_text')->nullable();
            $table->text('surat_laporan_raw_text')->nullable();

            $table->enum('status', [
                'menunggu',
                'diproses',
                'diterima',
                'ditolak',
                'selesai'
            ])->default('menunggu');

            $table->enum('leader_status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->text('leader_note')->nullable();
            $table->text('hrd_note')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('applications');
    }
}
