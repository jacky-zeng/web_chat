<?php

namespace App\Models;


class User extends \Illuminate\Foundation\Auth\User
{
    protected $guarded = ['id'];

    //定义头像 用于注册时随机取
    private static $avatars = [
        '/img/avatar/apple.jpg',
        '/img/avatar/en.png',
        '/img/avatar/haijiaoluoluo.jpg',
        '/img/avatar/jeff.gif',
        '/img/avatar/qianxing.jpg',
        '/img/avatar/qingsong.jpg',
        '/img/avatar/redsun.gif',
        '/img/avatar/wangnima.jpg'
    ];

    public static function createModel($params)
    {
        //随机取昵称和头像
        $index = rand(0, 7);

        $data_save = [
            'name'      => $params['name'],
            'password'  => bcrypt($params['password']),
            'nick_name' => $params['name'],
            'avatar'    => self::$avatars[$index],
        ];

        $mdl = self::create($data_save);

        return $mdl;
    }
}