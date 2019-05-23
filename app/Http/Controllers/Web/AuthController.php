<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Util\GenerateNickName;
use Illuminate\Http\Request;
use App\Util\Code;
use Auth;
use Redirect;
use Validator;
use Cache;

class AuthController extends Controller
{
    //游客登录
    public function touristLogin(Request $request, AuthRepository $authRepository)
    {
        $params = $request->all();

        if ($request->isMethod('post')) {
            $validator = Validator::make($params, [
                'nick_name' => 'required',
            ], [
                'nick_name.required' => '请设置您的大名',
            ]);

            if ($validator->fails()) {
                return Redirect::route('tourist_login')->withErrors($validator->errors()->first());
            }
            $rs = $authRepository->touristLogin($params['nick_name']);
            if (! $rs) {
                return Redirect::route('tourist_login')->withErrors($authRepository->firstMsg('进入系统失败'));
            }

            return Redirect::route('web_chat_chat')->withCookie($rs['key'], $rs['value']);
        } else {
            return view('web.auth.touristLogin', [
                'nick_name' => GenerateNickName::generate()
            ]);
        }
    }

    //生成随机游客名称
    public function getNickName()
    {
        return $this->successResponse('获取成功', [
            'nick_name' => GenerateNickName::generate()
        ]);
    }

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
        //记录登录时间
        User::find(\Illuminate\Support\Facades\Auth::guard('user-auth')->user()->id)->update([
            'logout_time' => date('Y-m-d H:i:s')
        ]);
        \Illuminate\Support\Facades\Auth::guard('user-auth')->logout();
        session()->flush();

        return Redirect::route('user_login', ['type' => 'login', 'time' => time()]);
    }
}