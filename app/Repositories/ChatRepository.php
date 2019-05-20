<?php

namespace App\Repositories;

use App\Models\ChatLog;
use App\Util\EnDecryption;
use App\Util\Errors;
use Illuminate\Support\Facades\Auth;
use DB;
use Validator;

class ChatRepository
{
    use Errors;

    /**
     * 聊天记录
     * @param $to_user_id
     * @return mixed
     */
    public function chatLog($to_user_id)
    {
        $chat_logs = ChatLog::where(function ($queryInner) use ($to_user_id) {
            $queryInner->where(function ($queryInnerInner) use ($to_user_id) {
                $queryInnerInner->where([
                    'user_id'    => Auth::guard('user-auth')->user()->id,
                    'to_user_id' => $to_user_id,
                ]);
            })->orWhere(function ($queryInnerInner) use ($to_user_id) {
                $queryInnerInner->where([
                    'user_id'    => $to_user_id,
                    'to_user_id' => Auth::guard('user-auth')->user()->id,
                ]);
            });
        })
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-3 day')))
            ->orderby('id', 'asc')
            ->get(['user_id', 'to_user_id', 'message', 'is_machine', 'created_at'])
            ->toArray();

        foreach ($chat_logs as $key => $chat_log) {
            $chat_logs[$key]['user_id']    = EnDecryption::encrypt($chat_log['user_id']);
            $chat_logs[$key]['to_user_id'] = EnDecryption::encrypt($chat_log['to_user_id']);
        }

        return $chat_logs;
    }
}