<?php

namespace App\Models;

class ChatLog extends \Illuminate\Foundation\Auth\User
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    //是否是机器人 0-否 1-是
    const IS_MACHINE_YES = 1;
    const IS_MACHINE_NO  = 0;

    public static function createModel($params)
    {
        $data_save = [
            'user_id'    => $params['user_id'],
            'to_user_id' => $params['to_user_id'],
            'is_machine' => array_get($params, 'is_machine') ? : self::IS_MACHINE_NO,
            'message'    => $params['message']
        ];

        $mdl = self::create($data_save);

        return $mdl;
    }
}