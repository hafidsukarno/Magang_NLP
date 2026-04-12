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
            $table->integer('duration')->default(3); // Durasi magang dalam bulan: 2, 3, 4, 5, dst
            $table->string('description')->nullable(); // Misal: "3 bulan - Batch A"
            $table->integer('position')->default(0); // Untuk sorting
            $table->timestamps();
            
            $table->unique(['department_id', 'duration']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_periods');
    }
}
