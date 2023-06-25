<?php

namespace App\Models;

class ChatGroupLog extends \Illuminate\Foundation\Auth\User
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    const TYPE_CONNECT = 0;    //客户端连接成功
    const TYPE_END = 1;        //牌局结束（有人胡了/没牌了）
    const TYPE_START = 2;      //游戏开始
    const TYPE_USER_GRAB = 3;  //用户抓牌
    const TYPE_USER_KNOCK = 4; //用户出牌
    const TYPE_AN_GANG = 5;    //暗杠
    const TYPE_GANG = 6;       //明杠
    const TYPE_CHI = 7;        //吃
    const TYPE_PENG = 8;       //碰
    const TYPE_NEXT = 9;       //轮到下一个

    //是否是机器人 0-否 1-是
    const IS_MACHINE_YES = 1;
    const IS_MACHINE_NO  = 0;

    //是否已发送 0-否 1-是
    const HAS_SEND_YES = 1;
    const HAS_SEND_NO  = 0;

    public static function createModel($params)
    {
        $data_save = [
            'group_num'  => $params['group_num'],
            'user_id'    => $params['user_id'],
            'type'       => $params['type'],
            'is_machine' => array_get($params, 'is_machine') ? : self::IS_MACHINE_NO,
            'message'    => \App\Util\GenerateNickName::xss($params['message']),
            'has_send'   => array_get($params, 'has_send') ? : self::HAS_SEND_NO
        ];

        $mdl = self::create($data_save);

        return $mdl;
    }

}
