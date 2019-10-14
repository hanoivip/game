<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGameCodeColumns extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('game_code')->default('');
        });
        Schema::table('recharges', function (Blueprint $table) {
            $table->string('game_code')->default('');
        });
        Schema::table('server_schedules', function (Blueprint $table) {
            $table->string('game_code')->default('');
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('game_code');
        });
        Schema::table('recharges', function (Blueprint $table) {
            $table->dropColumn('game_code');
        });
        Schema::table('server_schedules', function (Blueprint $table) {
            $table->dropColumn('game_code');
        });
    }
}
