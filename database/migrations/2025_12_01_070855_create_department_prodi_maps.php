<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('department_prodi_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('prodi_keyword');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_prodi_maps');
    }
};
