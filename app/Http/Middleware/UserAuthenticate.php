<?php

namespace App\Http\Middleware;

use App\Util\CacheKey;
use Closure;
use Illuminate\Support\Facades\Auth;
use Redis;
use Illuminate\Support\Facades\Cookie;
use Redirect;

class UserAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('user-auth')->guest()) {
            return $this->redirectToLogin($request);
        } else {
            //登录状态下 控制为单点登录
            $cache_key = sprintf(CacheKey::USER_SINGLE_LOGIN_KEY, Auth::guard('user-auth')->user()->id);
            $cookie    = Cookie::get($cache_key);
            $cache     = Redis::get($cache_key);
            if (! $cookie || ! $cache || $cookie != $cache) {
                return $this->redirectToLogin($request);
            }
        }

        return $next($request);
    }

    /**
     * 跳转到登录页面
     *
     * @param $request
     * @return \Illuminate\Http\Response
     */
    private function redirectToLogin($request)
    {
        Auth::guard('user-auth')->logout();
        session()->flush();
        if ($request->ajax()) {
            return response()->view('web.auth.redirect');
        } else {
            return Redirect::route('tourist_login');
        }
    }
}