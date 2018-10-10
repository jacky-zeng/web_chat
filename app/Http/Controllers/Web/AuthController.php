<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\AuthRepository;
use Illuminate\Http\Request;
use App\Util\Code;
use Auth;
use Redirect;
use Validator;
use Cache;

class AuthController extends Controller
{
    //注册
    public function register(Request $request, AuthRepository $authRepository)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'name'        => 'required',
            'password'    => 'required',
            're_password' => 'required'
        ], [
            'name.required'        => '用户名不能为空',
            'password.required'    => '密码不能为空',
            're_password.required' => '确认密码不能为空'
        ]);

        if ($validator->fails()) {
            return Redirect::route('user_login', ['type' => 'register'])->withErrors($validator->errors()->first());
        }
        $rs = $authRepository->register($params);
        if (! $rs) {
            return Redirect::route('user_login', ['type' => 'register'])->withErrors($authRepository->firstMsg('注册失败'));
        }

        return Redirect::route('web_chat_chat')->withCookie($rs['key'], $rs['value']);
    }

    // 登录/登录页
    public function login(Request $request, AuthRepository $authRepository)
    {
        $params = $request->all();

        if ($request->isMethod('post')) {
            $validator = Validator::make($params, [
                'name'     => 'required',
                'password' => 'required'
            ], [
                'name.required'     => '用户名不能为空',
                'password.required' => '密码不能为空'
            ]);

            if ($validator->fails()) {
                return Redirect::route('user_login', ['type' => 'login'])->withErrors($validator->errors()->first());
            }

            $rs = $authRepository->login($params);
            if (! $rs) {
                return Redirect::route('user_login', ['type' => 'login'])->withErrors($authRepository->firstMsg('登录失败'));
            }

            return Redirect::route('web_chat_chat')->withCookie($rs['key'], $rs['value']);
        }

        return view('web.auth.login');
    }

    //退出登录
    public function logout()
    {
        \Illuminate\Support\Facades\Auth::guard('user-auth')->logout();
        session()->flush();

        return Redirect::route('user_login', ['type' => 'login', 'time' => time()]);
    }
}