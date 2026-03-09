<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRpaResultsTable extends Migration
{
    public function up()
    {
        Schema::create('rpa_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->json('raw_json')->nullable();
            $table->text('extracted_text')->nullable();
            $table->json('fields')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('rpa_results');
    }
}
