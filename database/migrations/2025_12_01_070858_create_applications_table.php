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
            $table->string('registration_code')->unique();
            $table->string('type')->default('individual');
            $table->string('leader_name')->nullable();
            $table->string('leader_email')->nullable();
            $table->string('leader_phone')->nullable();
            $table->string('university')->nullable();
            $table->string('major')->nullable();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->string('duration')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('period')->nullable();
            $table->string('file_path');

            $table->integer('score')->nullable();
            $table->enum('status', [
                'menunggu',
                'diproses',
                'diterima',
                'ditolak',
                'pending',
                'selesai'
            ])->default('menunggu');

            $table->text('hrd_note')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('applications');
    }
}
