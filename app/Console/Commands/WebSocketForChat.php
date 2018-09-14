<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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

    public $ws_server;

    public $heart_beat_timer;

    const PORT = 9600;

    const PROCESS_NAME = 'WebSocketForChat';

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

        $this->ws_server->on('start', function ($ws_server) {
            $this->onStart($ws_server);
        });
        $this->ws_server->on('open', function ($ws_server, $request) {
            $this->onOpen($ws_server, $request);
        });
        $this->ws_server->on('message', function ($ws_server, $frame) {
            $this->onMessage($ws_server, $frame);
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
    }

    //关闭服务
    private function stop()
    {
        $this->info("========begin to stop web socket==========");
        //关闭心跳
        if ($this->heart_beat_timer) {
            swoole_timer_clear($this->heart_beat_timer);
        }
        //关闭进程
        $cmd_get_pid = 'pidof '.self::PROCESS_NAME;
        $pid         = shell_exec($cmd_get_pid);
        if ($pid) {
            $cmd_reload = "kill -9 $pid";
            shell_exec($cmd_reload);
            $this->info("========stop web socket success==========");
        } else {
            $this->info("error：web socket is not started");
        }
    }

    //启动在主进程的主线程回调
    private function onStart($ws_server)
    {
        //设置进程名
        swoole_set_process_name(self::PROCESS_NAME);
        //每2秒进行一次心跳检测，看看是不是挂了
        $this->heart_beat_timer = swoole_timer_tick(2000, function () {
            $this->heartBeat();
        });
        $this->info("========start web socket success==========");
    }

    //监听webSocket的连接事件
    private function onOpen($ws_server, $request)
    {
        $this->info("欢迎客户端 {$request->fd} 连接本服务器");
    }

    //监听webSocket的消息事件
    private function onMessage($ws_server, $frame)
    {
        //$frame->data，数据内容，可以是文本内容也可以是二进制数据，可以通过opcode的值来判断
        //$frame->opcode，WebSocket的OpCode类型，可以参考WebSocket协议标准文档
        //$frame->finish， 表示数据帧是否完整，一个WebSocket请求可能会分成多个数据帧进行发送（底层已经实现了自动合并数据帧，现在不用担心接收到的数据帧不完整）
        $this->info("客户端 {$frame->fd} 说:{$frame->data} (opcode:{$frame->opcode},finish:{$frame->finish})");
        $ws_server->push($frame->fd, '我是服务端，我已收到您的消息，您说的是：'.$frame->data);
    }

    //监听客户端关闭连接事件
    private function onClose($ws_server, $fd)
    {
        $this->info("客户端 {$fd} 已关闭连接");
    }

    //心跳检测
    private function heartBeat()
    {
        $cmd = "netstat -anp 2>/dev/null | grep ".self::PORT." |grep LISTEN | wc -l";

        $result = intval(shell_exec($cmd));
        if (! $result) {
            $this->info('is stopped!'.date('Y-m-d H:i:s'));
            //todo 发送邮件或短信通知
        } else {
            //$this->info('is Running'.date('Y-m-d H:i:s'));
        }
    }
}