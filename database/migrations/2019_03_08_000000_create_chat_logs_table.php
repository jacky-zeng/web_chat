<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(0)->comment('（当前发起聊天的）用户id');
            $table->integer('to_user_id')->default(0)->comment('（当前接收聊天的）用户id');
            $table->text('message')->comment('聊天内容');
            $table->tinyInteger('is_machine')->default(0)->comment('是否是机器人 0-否 1-是');
            $table->tinyInteger('has_send')->default(0)->comment('是否已发送 0-否 1-是');
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
        Schema::dropIfExists('chat_logs');
    }
}
