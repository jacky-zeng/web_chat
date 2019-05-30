<?php

namespace App\Models;

class ChatLog extends \Illuminate\Foundation\Auth\User
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    //是否是机器人 0-否 1-是
    const IS_MACHINE_YES = 1;
    const IS_MACHINE_NO  = 0;

    //是否已发送 0-否 1-是
    const HAS_SEND_YES = 1;
    const HAS_SEND_NO  = 0;

    public static function createModel($params)
    {
        $data_save = [
            'user_id'    => $params['user_id'],
            'to_user_id' => $params['to_user_id'],
            'is_machine' => array_get($params, 'is_machine') ? : self::IS_MACHINE_NO,
            'message'    => self::xss($params['message']),
            'has_send'   => array_get($params, 'has_send') ? : self::HAS_SEND_NO
        ];

        $mdl = self::create($data_save);

        return $mdl;
    }

    private static function xss($html)
    {
        //仅对script做了防xss处理
        $pattern = '/<script.*?<\/script>/';
        preg_match_all($pattern, $html, $matches);

        if(!empty($matches)) {
            foreach ($matches[0] as $match) {
                $temp_str = str_replace('<script>', '&lt;script&gt;', $match);
                $temp_str = str_replace('</script>', '&lt;/script&gt;', $temp_str);
                $html     = str_replace($match, $temp_str, $html);
            }
        }

        return $html;
    }
}