<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('password', 300);
            $table->string('nick_name', 50);
            $table->string('avatar', 200);
            $table->rememberToken();
            $table->dateTime('login_time')->default(null)->nullable()->comment('登录时间');
            $table->dateTime('logout_time')->default(null)->nullable()->comment('登出时间');
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
        Schema::dropIfExists('users');
    }
}
