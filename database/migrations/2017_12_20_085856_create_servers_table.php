<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('Tên máy chủ, viết liên không giấu. Không trùng. Làm định danh.');
            $table->string('ident')->comment('Chỉ số s1,s2,... Không trùng. Làm định danh. ');
            $table->string('title');
            $table->string('description');
            $table->string('login_uri');
            $table->string('recharge_uri');
            $table->string('operate_uri');
            $table->boolean('can_enter')->default(true);
            $table->string('gm_message')->default('');
            $table->boolean('is_hot')->default(true);
            $table->boolean('is_recommend')->default(false);
            $table->integer('max_online')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servers');
    }
}
