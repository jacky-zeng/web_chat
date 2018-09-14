<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}