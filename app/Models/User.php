<?php

namespace App\Models;

class User extends \Illuminate\Foundation\Auth\User
{
    protected $guarded = ['id'];

    //定义头像 用于注册时随机取
    private static $avatars = [
        '/img/avatar/a.jpg',
        '/img/avatar/b.jpg',
        '/img/avatar/c.jpg',
        '/img/avatar/d.jpg',
        '/img/avatar/e.jpg',
        '/img/avatar/f.jpg',
        '/img/avatar/g.jpg',
        '/img/avatar/h.jpg',
        '/img/avatar/i.jpg',
        '/img/avatar/j.jpg',
        '/img/avatar/k.jpg',
        '/img/avatar/l.jpg',
        '/img/avatar/m.jpg',
        '/img/avatar/n.jpg',
        '/img/avatar/o.jpg',
        '/img/avatar/p.jpg',
        '/img/avatar/q.jpg',
        '/img/avatar/r.jpg',
        '/img/avatar/s.jpg',
        '/img/avatar/t.jpg',
        '/img/avatar/u.jpg',
        '/img/avatar/v.jpg',
        '/img/avatar/w.jpg',
        '/img/avatar/x.jpg',
        '/img/avatar/y.jpg',
        '/img/avatar/z.jpg',
    ];

    public static function createModel($params)
    {
        //随机取昵称和头像
        $index = mt_rand(0, 25);

        $data_save = [
            'name'      => $params['name'],
            'password'  => bcrypt($params['password']),
            'nick_name' => $params['nick_name'],
            'avatar'    => self::$avatars[$index],
        ];

        $mdl = self::create($data_save);

        return $mdl;
    }
}