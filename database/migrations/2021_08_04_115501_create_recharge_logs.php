<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRechargeLogs extends Migration
{
    public function up()
    {
        Schema::create('recharge_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('order');
            $table->string('receipt');
            $table->integer('status')->default(0);;
            $table->integer('amount')->default(0);
            $table->integer('game_status')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recharge_logs');
    }
}
