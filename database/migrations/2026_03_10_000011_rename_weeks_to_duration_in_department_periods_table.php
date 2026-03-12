<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameWeeksToDurationInDepartmentPeriodsTable extends Migration
{
    public function up()
    {
        Schema::table('department_periods', function (Blueprint $table) {
            $table->renameColumn('weeks', 'duration');
        });
    }

    public function down()
    {
        Schema::table('department_periods', function (Blueprint $table) {
            $table->renameColumn('duration', 'weeks');
        });
    }
}
