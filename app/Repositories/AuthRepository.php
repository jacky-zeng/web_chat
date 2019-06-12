<?php

namespace App\Repositories;

use App\Models\User;
use App\Util\CacheKey;
use App\Util\Errors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Validator;

class AuthRepository
{
    use Errors;

    /**
     * 游客登录
     * @param $nick_name
     * @return array
     */
    public function touristLogin($nick_name)
    {
        $name = 'tourist_'.mt_rand(1000000, 9999999).time().mb_substr($nick_name, 0, 1);
        $user = User::where('name', $name)->first();
        if (! empty($user)) {
            $this->error('请重试');

            return false;
        }
        $user = User::createModel([
            'name'      => $name,
            'password'  => $name,
            'nick_name' => $nick_name
        ]);
        $single_token = $this->getSingleToken($user);

        return $single_token;
    }

    /**
     * 注册
     *
     * @param $params
     * @return bool|string
     */
    public function register($params)
    {
        $user = User::where('name', $params['name'])->first();

        if (! empty($user)) {
            $this->error('该用户名已存在');

            return false;
        }
        if ($params['password'] !== $params['re_password']) {
            $this->error('两次密码不一致');

            return false;
        }
        $params['nick_name'] = $params['name'];
        $user                = User::createModel($params);
        $single_token        = $this->getSingleToken($user);

        return $single_token;
    }

    /**
     * 登录
     *
     * @param $params
     * @return bool|string
     */
    public function login($params)
    {
        $user = User::where('name', $params['name'])->first();

        if (empty($user)) {
            $this->error('不存在该用户名');

            return false;
        }
        if (! password_verify($params['password'], array_get($user, 'password'))) {

            $this->error('用户名或密码错误');

            return false;
        } else {
            $single_token = $this->getSingleToken($user);

            return $single_token;
        }
    }

    /**
     * 获取单点登录的token 用于前端保存到cookie
     *
     * @param $user
     * @return array
     */
    private function getSingleToken($user)
    {
        Auth::guard('user-auth')->login($user);
        $user_id = Auth::guard('user-auth')->user()->id;
        //记录登录时间
        User::find($user_id)->update([
            'login_time' => date('Y-m-d H:i:s')
        ]);
        //单点登录数据存入  存入redis及返回cookie到前端 （单点登录过滤见app\Http\Middleware\AdminAuthenticate.php）
        $cache_key    = sprintf(CacheKey::USER_SINGLE_LOGIN_KEY, $user_id);
        $time         = time();
        $single_token = md5($user_id.$time);
        Redis::set($cache_key, $single_token);

        $rs = [
            'key'   => $cache_key,
            'value' => $single_token
        ];

        return $rs;
    }
}