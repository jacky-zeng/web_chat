<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\ChatRepository;
use App\Util\CacheKey;
use App\Util\EnDecryption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

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

    //聊天面板首页(测试麻将)
    public function chatChess()
    {
        return view('web.chat.chatChess');
    }

    //聊天记录
    public function chatLog(Request $request, ChatRepository $chatRepository)
    {
        $to_user_id = EnDecryption::decrypt($request->get('user_id'));

        $chat_logs = $chatRepository->chatLog($to_user_id);

        return $this->successResponse('获取成功', $chat_logs);
    }
}
