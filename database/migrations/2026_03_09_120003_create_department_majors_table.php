<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentMajorsTable extends Migration
{
    public function up()
    {
        Schema::create('department_majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('name'); // Misal: "Sistem Informasi", "Teknik Informatika"
            $table->text('description')->nullable();
            $table->integer('position')->default(0); // Untuk sorting
            $table->timestamps();
            
            $table->unique(['department_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_majors');
    }
}
