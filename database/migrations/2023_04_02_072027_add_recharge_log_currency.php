<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRechargeLogCurrency extends Migration
{
    public function up()
    {
        Schema::table('recharge_logs', function (Blueprint $table) {
            $table->string('currency')->default('');
        });
    }
    
    public function down()
    {
        Schema::table('recharge_logs', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
}
