<?php

namespace App\Console\Commands;

use App\Models\ChatLog;
use App\Models\User;
use App\Models\WebSocket;
use App\Util\CacheKey;
use App\Util\TuLingChat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Redis;
use App\Util\EnDecryption;

class WebSocketForChat extends Command
{
    /*
     * WebSocketForChat
     */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:chat {action?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'manager web socket for chat';

    private $ws_server;

    private $heart_beat_timer;

    const PORT = 9600;

    const PROCESS_NAME = 'swoole:chat_main';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'start':
                $this->start();
                break;
            case 'reload' :
                $this->reload();
                break;
            case 'stop' :
                $this->stop();
                break;
            default:
                $this->info("error：please input start,reload or stop!");
                break;
        }
    }

    //启动webSocket
    private function start()
    {
        $this->info("========begin to start web socket==========");
        $this->ws_server = new \swoole_websocket_server('0.0.0.0', self::PORT);
        $this->ws_server->set([
            'worker_num'      => 2, //一般设置为服务器CPU数的1-4倍
            'task_worker_num' => 4, //task进程的数量
        ]);

        $this->ws_server->on('start', function ($ws_server) {
            $this->onStart($ws_server);
        });
        $this->ws_server->on('open', function ($ws_server, $request) {
            $this->onOpen($ws_server, $request);
        });
        $this->ws_server->on('message', function ($ws_server, $frame) {
            $this->onMessage($ws_server, $frame);
        });
        $this->ws_server->on('task', function ($ws_server, $task_id, $from_id, $data) {
            $this->onTask($ws_server, $task_id, $from_id, $data);
        });
        $this->ws_server->on('finish', function ($ws_server, $task_id, $data) {
            $this->onFinish($ws_server, $task_id, $data);
        });
        $this->ws_server->on('close', function ($request, $response) {
            $this->onClose($request, $response);
        });
        $this->ws_server->start();
    }

    //平滑重启服务 重启所有worker进程 具体见 https://wiki.swoole.com/wiki/page/p-server/reload.html
    private function reload()
    {
        $this->info("========begin to reload web socket==========");

        $server_info = php_uname();
        if(strpos($server_info, 'Linux') === 0) {
            //重启进程
            $cmd_get_pid = 'pidof '.self::PROCESS_NAME;
            $pid         = shell_exec($cmd_get_pid);
            if ($pid) {
                $cmd_reload = "kill -USR1 $pid";
                shell_exec($cmd_reload);
                $this->info("========reload web socket success==========");
            } else {
                $this->info("error：web socket is not started");
            }
        } elseif (strpos($server_info, 'Darwin') === 0) {
            $this->info("ERROR:不支持该系统");
        } else {
            $this->info("ERROR:不支持该系统");
        }
    }

    //关闭服务
    private function stop()
    {
        $this->info("========begin to stop web socket==========");

        $server_info = php_uname();
        if(strpos($server_info, 'Linux') === 0) {
            //关闭心跳
            if ($this->heart_beat_timer) {
                swoole_timer_clear($this->heart_beat_timer);
            }
            $cmd_stop = "ps -ef|grep swoole:chat|grep -v grep|cut -c 9-15|xargs kill -9";
            $this->info("========just kill all of the websocket for chat process==========");
            shell_exec($cmd_stop);
            $this->info("========start web socket success==========");
        } elseif (strpos($server_info, 'Darwin') === 0) {
            //关闭心跳
            if ($this->heart_beat_timer) {
                swoole_timer_clear($this->heart_beat_timer);
            }
            $cmd_stop = "ps -ef|grep swoole:chat|grep -v grep|cut -c 7-11|xargs kill -9";
            $this->info("========just kill all of the websocket for chat process==========");
            shell_exec($cmd_stop);
        } else {
            $this->info("ERROR:不支持该系统");
        }
    }

    //启动在主进程的主线程回调
    private function onStart($ws_server)
    {
        $server_info = php_uname();
        if(strpos($server_info, 'Linux') === 0) {
            //设置进程名
            swoole_set_process_name(self::PROCESS_NAME);
            //每2秒进行一次心跳检测，看看是不是挂了
            $this->heart_beat_timer = swoole_timer_tick(2000, function () {
                $this->heartBeat();
            });
            $this->info("========start web socket success==========");
        } elseif (strpos($server_info, 'Darwin') === 0) {
            //每2秒进行一次心跳检测，看看是不是挂了
            $this->heart_beat_timer = swoole_timer_tick(2000, function () {
                $this->heartBeat();
            });
            $this->info("========start web socket success==========");
        } else {
            $this->info("ERROR:不支持该系统");
        }
    }

    //监听webSocket的连接事件
    private function onOpen($ws_server, $request)
    {
        $this->info("欢迎客户端 {$request->fd} 连接本服务器");
        $connect_success = true;
        try {
            $en_user_id = $request->get['user_id'];
            $user_id    = EnDecryption::decrypt($en_user_id);
            $token      = decrypt($request->get['token']);
            $cache_key  = sprintf(CacheKey::USER_SINGLE_LOGIN_KEY, $user_id);
            $real_token = Redis::get($cache_key);
            if ($real_token != $token) {
                $connect_success = false;
                $this->info("客户端 {$request->fd} 鉴权失败");
                $ws_server->disconnect($request->fd, 1000, '鉴权失败');
            }
        } catch (\Exception $ex) {
            $connect_success = false;
            $this->info("客户端 {$request->fd} 鉴权失败");
            $ws_server->disconnect($request->fd, 1000, '鉴权失败');
        }
        if ($connect_success) {
            //1.用户id跟websocket的fd做绑定（存入redis）
            $user      = User::find($user_id);
            $user_data = [
                'nick_name' => $user->nick_name,
                'avatar'    => $user->avatar,
                'fd'        => $request->fd
            ];
            Redis::hSet(CacheKey::USER_IDS_KEY, $en_user_id, json_encode($user_data));
            //2.获取用户列表
            $user_list = $this->getUserList($ws_server);
            $ws_server->push($request->fd, WebSocket::TYPE_USER_LIST.WebSocket::SPLIT_WORD.json_encode($user_list));
            //3.task广播：用户列表加入一个用户
            foreach ($ws_server->connections as $connect_fd) {
                $task_data = [
                    'type' => WebSocket::TYPE_USER_LOGIN,
                    'fd'   => $connect_fd,
                    'data' => [
                        $en_user_id => $user_data
                    ]
                ];
                $ws_server->task($task_data);
            }
            //4.离线信息发送给当前登录用户
            $this->sendOfflineMsg($ws_server, $user_id);
        }
    }

    //监听webSocket的消息事件
    private function onMessage($ws_server, $frame)
    {
        //$frame->data，数据内容，可以是文本内容也可以是二进制数据，可以通过opcode的值来判断
        //$frame->opcode，WebSocket的OpCode类型，可以参考WebSocket协议标准文档
        //$frame->finish， 表示数据帧是否完整，一个WebSocket请求可能会分成多个数据帧进行发送（底层已经实现了自动合并数据帧，现在不用担心接收到的数据帧不完整）
        //$this->info("客户端 {$frame->fd} 说:{$frame->data} (opcode:{$frame->opcode},finish:{$frame->finish})");
        $data      = json_decode($frame->data, true);
        $task_data = [
            'type' => WebSocket::TYPE_MSG,
            'data' => $data
        ];
        $ws_server->task($task_data);
    }

    //监听webSocket的任务事件
    private function onTask($ws_server, $task_id, $from_id, $data)
    {
        switch ($data['type']) {
            case WebSocket::TYPE_USER_LOGIN:
                $ws_server->push($data['fd'], WebSocket::TYPE_USER_LOGIN.WebSocket::SPLIT_WORD.json_encode($data['data']));
                break;
            case WebSocket::TYPE_USER_LOGOUT:
                $ws_server->push($data['fd'], WebSocket::TYPE_USER_LOGOUT.WebSocket::SPLIT_WORD.json_encode($data['data']));
                break;
            case WebSocket::TYPE_MSG:
                if($data['data']['from_user_id'] == $data['data']['to_user_id']){
                    //图灵机器人需要user_id 可能有限制 所以分配100个
                    $tu_ling_user_id = EnDecryption::decrypt($data['data']['from_user_id']) % 100;
                    //记录聊天记录
                    $data_save = [
                        'user_id'    => EnDecryption::decrypt($data['data']['from_user_id']),
                        'to_user_id' => EnDecryption::decrypt($data['data']['to_user_id']),
                        'message'    => $data['data']['message']
                    ];
                    ChatLog::createModel($data_save);
                    //使用聊天机器人
                    $messages = TuLingChat::ask($tu_ling_user_id, $data['data']['message']);
                    foreach ($messages as $message){
                        $send       = [
                            'from_user_id' => $data['data']['from_user_id'],
                            'to_user_id'   => $data['data']['to_user_id'],
                            'date'         => date('Y-m-d H:i:s'),
                            'message'      => $message
                        ];
                        $to_user_id = $data['data']['to_user_id'];
                        $to_user    = Redis::hGet(CacheKey::USER_IDS_KEY, $to_user_id);
                        $to_fd      = array_get(json_decode($to_user, true), 'fd');
                        //$this->info('发送聊天信息:'.$to_fd.'|'.WebSocket::TYPE_MSG.WebSocket::SPLIT_WORD.json_encode($send));
                        $ws_server->push($to_fd, WebSocket::TYPE_MSG.WebSocket::SPLIT_WORD.json_encode($send));
                        //记录聊天记录
                        $data_save = [
                            'user_id'    => EnDecryption::decrypt($data['data']['from_user_id']),
                            'to_user_id' => EnDecryption::decrypt($data['data']['to_user_id']),
                            'is_machine' => ChatLog::IS_MACHINE_YES,
                            'message'    => $message
                        ];
                        ChatLog::createModel($data_save);
                    }
                }else{
                    $send       = [
                        'from_user_id' => $data['data']['from_user_id'],
                        'to_user_id'   => $data['data']['to_user_id'],
                        'date'         => date('Y-m-d H:i:s'),
                        'message'      => '<xmp>'.$data['data']['message'].'</xmp>'
                    ];
                    $to_user_id = $data['data']['to_user_id'];
                    $to_user    = Redis::hGet(CacheKey::USER_IDS_KEY, $to_user_id);
                    $to_fd      = array_get(json_decode($to_user, true), 'fd');
                    //$this->info('发送聊天信息:'.$to_fd.'|'.WebSocket::TYPE_MSG.WebSocket::SPLIT_WORD.json_encode($send));
                    if($to_fd) { //用户在线 才发送
                        try {
                            $ws_server->push($to_fd, WebSocket::TYPE_MSG.WebSocket::SPLIT_WORD.json_encode($send));
                        } catch (\Exception $ex) {
                            Redis::hDel(CacheKey::USER_IDS_KEY, $to_user_id);
                            $to_fd = false;
                        }
                    }
                    //记录聊天记录
                    $data_save = [
                        'user_id'    => EnDecryption::decrypt($data['data']['from_user_id']),
                        'to_user_id' => EnDecryption::decrypt($data['data']['to_user_id']),
                        'message'    => $data['data']['message'],
                        'has_send'   => $to_fd ? ChatLog::HAS_SEND_YES : ChatLog::HAS_SEND_NO
                    ];
                    ChatLog::createModel($data_save);
                }
                break;
            case WebSocket::TYPE_OFFLINE_MSG:
                $send       = [
                    'from_user_id' => $data['data']['from_user_id'],
                    'to_user_id'   => $data['data']['to_user_id'],
                    'date'         => $data['data']['date'],
                    'message'      => '<xmp>'.$data['data']['message'].'</xmp>'
                ];
                $to_user_id = $data['data']['to_user_id'];
                $to_user    = Redis::hGet(CacheKey::USER_IDS_KEY, $to_user_id);
                $to_fd      = array_get(json_decode($to_user, true), 'fd');
                //$this->info('发送聊天信息:'.$to_fd.'|'.WebSocket::TYPE_MSG.WebSocket::SPLIT_WORD.json_encode($send));
                if($to_fd) { //用户在线 才发送
                    $ws_server->push($to_fd, WebSocket::TYPE_MSG.WebSocket::SPLIT_WORD.json_encode($send));
                    ChatLog::where('id', $data['data']['id'])->update([
                        'has_send' => ChatLog::HAS_SEND_YES
                    ]);
                }
                break;
            default:
                break;
        }
    }

    //监听webSocket的任务完成事件
    private function onFinish($ws_server, $frame)
    {
    }

    //监听客户端关闭连接事件
    private function onClose($ws_server, $fd)
    {
        $this->info("客户端 {$fd} 已关闭连接");
        //1.找出该用户id，并从redis中删除该用户
        $en_user_id        = 0;
        $redis_en_user_ids = Redis::hGetAll(CacheKey::USER_IDS_KEY);
        foreach ($redis_en_user_ids as $redis_en_user_id => $user) {
            $user = json_decode($user, true);
            if ($user['fd'] == $fd) {
                $en_user_id = $redis_en_user_id;
            }
        }
        Redis::hDel(CacheKey::USER_IDS_KEY, $en_user_id);
        //2.task广播：用户列表删除一个用户
        foreach ($ws_server->connections as $connect_fd) {
            if ($connect_fd != $fd) {
                $task_data = [
                    'type' => WebSocket::TYPE_USER_LOGOUT,
                    'fd'   => $connect_fd,
                    'data' => [
                        $en_user_id => $en_user_id
                    ]
                ];
                $ws_server->task($task_data);
            }
        }
    }

    //心跳检测
    private function heartBeat()
    {
        $server_info = php_uname();
        if(strpos($server_info, 'Linux') === 0) {
            $cmd = "netstat -anp 2>/dev/null | grep ".self::PORT." |grep LISTEN | wc -l";

            $result = intval(shell_exec($cmd));
            if (! $result) {
                $this->info('is stopped!'.date('Y-m-d H:i:s'));
                swoole_async_writefile('heartBeat.log', 'swoole:chat is stopped! '.date('Y-m-d H:i:s'), function () {
                    echo 'write ok';
                });
                //todo 发送邮件或短信通知
            } else {
                //$this->info('is Running'.date('Y-m-d H:i:s'));
            }
        } elseif (strpos($server_info, 'Darwin') === 0) {
            $cmd = "lsof -nP -i:".self::PORT." | grep ".self::PORT." |grep LISTEN | wc -l";

            $result = intval(shell_exec($cmd));
            if (! $result) {
                $this->info('is stopped!'.date('Y-m-d H:i:s'));
                swoole_async_writefile('heartBeat.log', 'swoole:chat is stopped! '.date('Y-m-d H:i:s'), function () {
                    echo 'write ok';
                });
                //todo 发送邮件或短信通知
            } else {
                //$this->info('is Running'.date('Y-m-d H:i:s'));
            }
        } else {
            $this->info("ERROR:不支持该系统");
        }
    }

    /**
     * 获取服务器用户列表
     * @param $ws_server
     * @return array
     */
    private function getUserList($ws_server)
    {
        $user_list = [];
        //获取在线用户
        $redis_en_user_ids = Redis::hGetAll(CacheKey::USER_IDS_KEY);
        foreach ($redis_en_user_ids as $redis_en_user_id => $redis_user) {
            $redis_user = json_decode($redis_user, true);
            foreach ($ws_server->connections as $connect_fd) {
                if ($redis_user['fd'] == $connect_fd) {
                    $redis_user['is_online'] = 1;
                    $user_list[$redis_en_user_id] = $redis_user;
                }
            }
        }
        //获取三天内的活跃用户
        $format_users = [];
        $users = User::where('login_time', '>=', date('Y-m-d H:i:s', strtotime('-3 day')))
            ->orWhere('logout_time', '>=', date('Y-m-d H:i:s', strtotime('-3 day')))
            ->get(['id', 'nick_name', 'avatar']);
        foreach ($users as $user) {
            $format_users[EnDecryption::encrypt($user['id'])] = [
                'nick_name' => $user['nick_name'],
                'avatar'    => $user['avatar'],
                'is_online' => 0                    //不在线
            ];
        }
        //合并
        $rs_user_list = array_merge($format_users, $user_list); //$user_list必须放在后面，出现重复key时，$user_list会覆盖掉$format_users中的数据

        return $rs_user_list;
    }

    /**
     * 发送离线消息
     * @param $ws_server
     * @param $user_id
     */
    private function sendOfflineMsg($ws_server, $user_id)
    {
        $chat_logs = ChatLog::where([
            'to_user_id' => $user_id,
            'has_send'   => ChatLog::HAS_SEND_NO,
            'is_machine' => ChatLog::IS_MACHINE_NO
        ])
            ->where('user_id', '<>', $user_id)
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-1 day')))  //1天前
            ->orderby('id', 'asc')
            ->get(['id', 'user_id', 'to_user_id', 'message', 'created_at'])
            ->toArray();

        foreach ($chat_logs as $chat_log) {
            $task_data = [
                'type' => WebSocket::TYPE_OFFLINE_MSG,
                'data' => [
                    'id'           => $chat_log['id'],
                    'from_user_id' => EnDecryption::encrypt($chat_log['user_id']),
                    'to_user_id'   => EnDecryption::encrypt($user_id),
                    'message'      => $chat_log['message'],
                    'date'         => $chat_log['created_at'],
                ]
            ];
            $ws_server->task($task_data);
        }
    }
}