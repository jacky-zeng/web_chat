<?php

namespace App\Models;

class ChatGroupLog extends \Illuminate\Foundation\Auth\User
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    const TYPE_INIT_WHOLE = 1; //获取整套牌

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
