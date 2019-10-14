<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('server_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('action');
            $table->integer('action_time');
            $table->string('action_summary');
            $table->string('server_name');
            $table->string('server_title');
            $table->string('server_params');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('server_schedules');
    }
}
