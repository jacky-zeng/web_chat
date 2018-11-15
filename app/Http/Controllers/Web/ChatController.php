<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Util\CacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Redis;

class ChatController extends Controller
{
    //聊天面板首页
    public function index(Request $request)
    {
        $params = $request->all();

        return view('web.chat.index', [
            'params' => json_encode($params),
        ]);
    }

    //聊天面板首页(完善版)
    public function chat()
    {
        $cache_key = sprintf(CacheKey::USER_SINGLE_LOGIN_KEY, Auth::guard('user-auth')->user()->id);
        $token     = Redis::get($cache_key);

        return view('web.chat.chat', [
            'token' => encrypt($token)
        ]);
    }
}