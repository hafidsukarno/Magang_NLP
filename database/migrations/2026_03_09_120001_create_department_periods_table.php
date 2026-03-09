<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentPeriodsTable extends Migration
{
    public function up()
    {
        Schema::create('department_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->integer('weeks'); // 4, 5, 6, atau custom value
            $table->string('description')->nullable(); // Misal: "4 minggu - Kategori A"
            $table->integer('position')->default(0); // Untuk sorting
            $table->timestamps();
            
            $table->unique(['department_id', 'weeks']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_periods');
    }
}
