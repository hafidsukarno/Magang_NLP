<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentSkillsTable extends Migration
{
    public function up()
    {
        Schema::create('department_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('name'); // Misal: "PHP", "Database Design", "UI/UX"
            $table->string('level')->nullable(); // Misal: "Beginner", "Intermediate", "Expert"
            $table->text('description')->nullable();
            $table->integer('position')->default(0); // Untuk sorting
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_skills');
    }
}
