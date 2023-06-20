<?php

namespace App\Console\Commands;

use App\Models\ChatGroupLog;
use App\Models\User;
use App\Models\WebSocket;
use App\Repositories\ChessRepository;
use App\Util\CacheKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class WebSocketForChatChess extends Command
{
    /*
     * WebSocketForChatChess
     */

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'swoole:chat_chess {action?}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'manager web socket for chat_chess';

    private $ws_server;

    private $heart_beat_timer;

    const PORT = 9600;

    const PROCESS_NAME = 'swoole:chat_chess';

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
        if (strpos($server_info, 'Linux') === 0) {
            //重启进程
            $cmd_get_pid = 'pidof ' . self::PROCESS_NAME;
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
        if (strpos($server_info, 'Linux') === 0) {
            //关闭心跳
            if ($this->heart_beat_timer) {
                swoole_timer_clear($this->heart_beat_timer);
            }
            $cmd_stop = "ps -ef|grep swoole:chat_chess|grep -v grep|cut -c 9-16|xargs kill -9";
            $this->info("========just kill all of the websocket for chat_chess process==========");
            shell_exec($cmd_stop);
            $this->info("========start web socket success==========");
        } elseif (strpos($server_info, 'Darwin') === 0) {
            //关闭心跳
            if ($this->heart_beat_timer) {
                swoole_timer_clear($this->heart_beat_timer);
            }
            $cmd_stop = "ps -ef|grep swoole:chat_chess|grep -v grep|cut -c 7-11|xargs kill -9";
            $this->info("========just kill all of the websocket for chat_chess process==========");
            shell_exec($cmd_stop);
        } else {
            $this->info("ERROR:不支持该系统");
        }
    }

    //启动在主进程的主线程回调
    private function onStart($ws_server)
    {
        $server_info = php_uname();
        if (strpos($server_info, 'Linux') === 0) {
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
            /** 用户开麻将房或者加入牌桌：一个房间只有一个开房间的人，且，一个房间最多4人 **/
            $group_num        = $request->get['group_num'] ?? 0;        //创建牌桌号
            $join_group_num   = $request->get['join_group_num'] ?? 0;   //加入牌桌号
            $device_unique_id = $request->get['device_unique_id'] ?? 0; //设备唯一id
            if (empty($device_unique_id)) {
                $connect_success = false;
                $this->info("客户端 {$request->fd} 鉴权失败(empty device)");
                $send = [
                    'type'    => -1,
                    'message' => '鉴权失败(empty device)',
                    'date'    => date('Y-m-d H:i:s')
                ];
                $ws_server->push($request->fd, json_encode($send));
                $ws_server->disconnect($request->fd, 1000, '鉴权失败(empty device)');
            }
            if (empty($group_num) && empty($join_group_num)) {
                $connect_success = false;
                $this->info("客户端 {$request->fd} 牌桌失败");
                $send = [
                    'type'    => -1,
                    'message' => '牌桌失败',
                    'date'    => date('Y-m-d H:i:s')
                ];
                $ws_server->push($request->fd, json_encode($send));
                $ws_server->disconnect($request->fd, 1000, '牌桌失败');
            }
            if (!empty($group_num)) { //创建牌桌
                $redis_en_user_ids = Redis::hGetAll(sprintf(CacheKey::GROUP_USER_IDS_KEY, $group_num));
                if (!empty($redis_en_user_ids)) {
                    $connect_success = false;
                    $this->info("客户端 {$request->fd} 创建牌桌失败，牌桌已存在");
                    $send = [
                        'type'    => -1,
                        'message' => '创建牌桌失败，牌桌已存在' . json_encode($redis_en_user_ids),
                        'date'    => date('Y-m-d H:i:s')
                    ];
                    $ws_server->push($request->fd, json_encode($send));
                    $ws_server->disconnect($request->fd, 1000, '创建牌桌失败，牌桌已存在');
                } else {
                    $user = User::createOrUpdate($device_unique_id, $group_num);
                    Redis::set(sprintf(CacheKey::DEVICE_UNIQUE_ID_KEY, $device_unique_id), json_encode([
                        'user_id'  => $user['id'],
                        //'is_owner' => 1
                    ]));
                    Redis::set(sprintf(CacheKey::FD_KEY, $request->fd), json_encode([
                        'user_id'   => $user['id'],
                        'group_num' => $group_num
                    ]));
                    $send = [
                        'type'    => ChatGroupLog::TYPE_CONNECT,
                        'message' => 'create success',
                        'date'    => date('Y-m-d H:i:s')
                    ];
                    $ws_server->push($request->fd, json_encode($send));
                    $connect_success = true;
                }
            } elseif (!empty($join_group_num)) { //加入牌桌
                $redis_en_user_ids = Redis::hGetAll(sprintf(CacheKey::GROUP_USER_IDS_KEY, $join_group_num));
                if (empty($redis_en_user_ids)) {
                    $connect_success = false;
                    $this->info("客户端 {$request->fd} 加入牌桌失败，牌桌不存在");
                    $send = [
                        'type'    => -1,
                        'message' => '加入牌桌失败，牌桌不存在',
                        'date'    => date('Y-m-d H:i:s')
                    ];
                    $ws_server->push($request->fd, json_encode($send));
                    $ws_server->disconnect($request->fd, 1000, '加入牌桌失败，牌桌不存在');
                } elseif (count($redis_en_user_ids) >= 4) {
                    $connect_success = false;
                    $this->info("客户端 {$request->fd} 加入牌桌失败，牌桌已满");
                    $send = [
                        'type'    => -1,
                        'message' => '加入牌桌失败，牌桌已满',
                        'date'    => date('Y-m-d H:i:s')
                    ];
                    $ws_server->push($request->fd, json_encode($send));
                    $ws_server->disconnect($request->fd, 1000, '加入牌桌失败，牌桌已满');
                } else {
                    $user = User::createOrUpdate($device_unique_id, $join_group_num);
                    Redis::set(sprintf(CacheKey::DEVICE_UNIQUE_ID_KEY, $device_unique_id), json_encode([
                        'user_id'  => $user['id'],
                        //'is_owner' => 0
                    ]));
                    Redis::set(sprintf(CacheKey::FD_KEY, $request->fd), json_encode([
                        'user_id'   => $user['id'],
                        'group_num' => $join_group_num
                    ]));
                    $send = [
                        'type'    => 0,
                        'message' => 'join success',
                        'date'    => date('Y-m-d H:i:s')
                    ];
                    $ws_server->push($request->fd, json_encode($send));
                    $connect_success = true;
                }
            }
        } catch (\Exception $ex) {
            $connect_success = false;
            $this->info("客户端 {$request->fd} 鉴权失败(Exception)" . $ex->getMessage() . $ex->getTraceAsString() . $ex->getLine());
            $ws_server->disconnect($request->fd, 1000, '鉴权失败(Exception)');
        }
        if ($connect_success) {
            $group_num = $group_num ? $group_num : ($join_group_num ? $join_group_num : 0);
            $user_id   = $user['id'];

            //1.用户id跟websocket的fd做绑定（存入redis）
            $user_data = [
                'group_num' => $group_num,
                'name'      => $user['name'],
                'fd'        => $request->fd
            ];
            Redis::hSet(sprintf(CacheKey::GROUP_USER_IDS_KEY, $group_num), $user_id, json_encode($user_data));

            $redis_en_user_ids = Redis::hGetAll(sprintf(CacheKey::GROUP_USER_IDS_KEY, $group_num));
            if (count($redis_en_user_ids) == 1) { //todo  ==4
                //开始游戏
                $initCards = (new ChessRepository())->initCards();
                $this->info(substr($initCards, 0, 200));
                $message = $initCards;

                $realUserDiceSide = 4;  //todo
                $isOnlineSide     = []; //在线的用户方位
                foreach ($redis_en_user_ids as $redis_en_user_id => $redis_user) {
                    $isOnlineSide[] = $realUserDiceSide;
                    --$realUserDiceSide;
                }
                $realUserDiceSide = 4; //todo
                foreach ($redis_en_user_ids as $redis_en_user_id => $redis_user) {
                    $redis_user = json_decode($redis_user, true);
                    $send       = [
                        'type'    => ChatGroupLog::TYPE_START,
                        'message' => $realUserDiceSide . '|' . $message . '|' . implode('#', $isOnlineSide),
                        'date'    => date('Y-m-d H:i:s')
                    ];

                    $ws_server->push($redis_user['fd'], json_encode($send));
                    $this->info($redis_user['fd'] . '|user_id=' . $redis_en_user_id . '|realUserDiceSide=' . $realUserDiceSide);

                    //将用户的真实方位，存入用户对象
                    $userInfo                        = json_decode(Redis::get(sprintf(CacheKey::DEVICE_UNIQUE_ID_KEY, $device_unique_id)), true);
                    $userInfo['real_user_dice_side'] = $realUserDiceSide;
                    Redis::set(sprintf(CacheKey::DEVICE_UNIQUE_ID_KEY, $device_unique_id), json_encode($userInfo));

                    --$realUserDiceSide;
                }
            }
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
                //$ws_server->push($data['fd'], WebSocket::TYPE_USER_LOGIN . WebSocket::SPLIT_WORD . json_encode($data['data']));
                break;
            case WebSocket::TYPE_USER_LOGOUT:
                //$ws_server->push($data['fd'], WebSocket::TYPE_USER_LOGOUT . WebSocket::SPLIT_WORD . json_encode($data['data']));
                break;
            case WebSocket::TYPE_MSG:
                if (empty($data['data']['group_num']) || empty($data['data']['type'])) {
                    $this->info("客户端发了：空的" . json_encode($data));
                    break;
                }
                $type = $data['data']['type'];
                $group_num = $data['data']['group_num'];
                $device_unique_id = $data['data']['device_unique_id'];
                $message = $data['data']['message'];

                $this->info("客户端发了：" . $type.'|'.$group_num.'|'.$device_unique_id.'|'.$message);

                $userInfo = json_decode(Redis::get(sprintf(CacheKey::DEVICE_UNIQUE_ID_KEY, $device_unique_id)), true);
                //$from_user_id = $userInfo['user_id'];
                $realUserDiceSide = $userInfo['real_user_dice_side'];
                switch ($type) {
                    case ChatGroupLog::TYPE_END:
                        $winList = $message;
                        $redis_en_user_ids = Redis::hGetAll(sprintf(CacheKey::GROUP_USER_IDS_KEY, $group_num));

                        foreach ($redis_en_user_ids as $redis_en_user_id => $redis_user) {
                            $redis_user = json_decode($redis_user, true);

                            $send = [
                                'type'    => $type,
                                'message' => $winList,
                                'date'    => date('Y-m-d H:i:s')
                            ];

                            $ws_server->push($redis_user['fd'], json_encode($send));
                            $this->info($redis_user['fd'].'|user_id='.$redis_en_user_id . '|message='. $send['message']);
                        }
                        break;
                    case ChatGroupLog::TYPE_USER_GRAB:
                        $messages = explode('|', $message);
                        $realActivityDiceSide = $messages[0];
                        $keyGrab = $messages[1];
                        $redis_en_user_ids = Redis::hGetAll(sprintf(CacheKey::GROUP_USER_IDS_KEY, $group_num));

                        foreach ($redis_en_user_ids as $redis_en_user_id => $redis_user) {
                            $redis_user = json_decode($redis_user, true);

                            $send = [
                                'type'    => $type,
                                'message' => $realActivityDiceSide.'|'. $realUserDiceSide . '|' . $keyGrab,
                                'date'    => date('Y-m-d H:i:s')
                            ];

                            $ws_server->push($redis_user['fd'], json_encode($send));
                            $this->info($redis_user['fd'].'|user_id='.$redis_en_user_id . '|message='. $send['message']);
                        }
                        break;
                    case ChatGroupLog::TYPE_USER_KNOCK:
                        $messages = explode('|', $message);
                        $realActivityDiceSide = $messages[0];
                        $keyKnock = $messages[1];
                        $redis_en_user_ids = Redis::hGetAll(sprintf(CacheKey::GROUP_USER_IDS_KEY, $group_num));

                        foreach ($redis_en_user_ids as $redis_en_user_id => $redis_user) {
                            $redis_user = json_decode($redis_user, true);

                            $send = [
                                'type'    => $type,
                                'message' => $realActivityDiceSide.'|'. $realUserDiceSide . '|' . $keyKnock,
                                'date'    => date('Y-m-d H:i:s')
                            ];

                            $ws_server->push($redis_user['fd'], json_encode($send));
                            $this->info($redis_user['fd'].'|user_id='.$redis_en_user_id . '|message='. $send['message']);
                        }
                        break;

                    default:
                        break;
                }


                //$send      = [
                //    'type'    => ChatGroupLog::TYPE_PREPARE,
                //    'message' => $message,
                //    'date'    => date('Y-m-d H:i:s')
                //];
                //$group_num = $data['data']['group_num'];
                //$to_user   = Redis::hGet(sprintf(CacheKey::GROUP_USER_IDS_KEY, $group_num), $from_user_id);
                //$to_fd     = array_get(json_decode($to_user, true), 'fd');

                //if ($to_fd) { //用户在线 才发送
                //    try {
                //        $ws_server->push($to_fd, json_encode($send));
                //    } catch (\Exception $ex) {
                //        Redis::hDel(sprintf(CacheKey::GROUP_USER_IDS_KEY, $group_num), $from_user_id);
                //        $to_fd = false;
                //    }
                //}
                ////记录聊天记录
                //$data_save = [
                //    'group_num' => $data['data']['group_num'],
                //    'user_id'   => $from_user_id,
                //    'type'      => 0,
                //    'message'   => $message,
                //    'has_send'  => $to_fd ? ChatGroupLog::HAS_SEND_YES : ChatGroupLog::HAS_SEND_NO
                //];
                //ChatGroupLog::createModel($data_save);
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
        $user = Redis::get(sprintf(CacheKey::FD_KEY, $fd));

        if ($user) {
            $user = json_decode($user, true);
            Redis::hDel(sprintf(CacheKey::GROUP_USER_IDS_KEY, $user['group_num']), $user['user_id']);
            //2.task广播：用户列表删除一个用户
            $redis_en_user_ids = Redis::hGetAll(sprintf(CacheKey::GROUP_USER_IDS_KEY, $user['group_num']));
            $this->info(json_encode($redis_en_user_ids));
            foreach ($redis_en_user_ids as $redis_en_user_id => $redis_user) {
                $redis_user = json_decode($redis_user, true);
                $task_data  = [
                    'type' => WebSocket::TYPE_USER_LOGOUT,
                    'fd'   => $redis_user['fd'],
                    'data' => [
                        $user['user_id'] => $user['user_id']
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
        if (strpos($server_info, 'Linux') === 0) {
            $cmd = "netstat -anp 2>/dev/null | grep " . self::PORT . " |grep LISTEN | wc -l";

            $result = intval(shell_exec($cmd));
            if (!$result) {
                $this->info('is stopped!' . date('Y-m-d H:i:s'));
                swoole_async_writefile('heartBeat.log', 'swoole:chat_chess is stopped! ' . date('Y-m-d H:i:s'), function () {
                    echo 'write ok';
                });
                //todo 发送邮件或短信通知
            } else {
                //$this->info('is Running'.date('Y-m-d H:i:s'));
            }
        } elseif (strpos($server_info, 'Darwin') === 0) {
            $cmd = "lsof -nP -i:" . self::PORT . " | grep " . self::PORT . " |grep LISTEN | wc -l";

            $result = intval(shell_exec($cmd));
            if (!$result) {
                $this->info('is stopped!' . date('Y-m-d H:i:s'));
                swoole_async_writefile('heartBeat.log', 'swoole:chat_chess is stopped! ' . date('Y-m-d H:i:s'), function () {
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

}
