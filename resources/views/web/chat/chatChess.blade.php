<head>
    <meta charset="UTF-8">
    <title>麻将测试 首页</title>
    <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
</head>
<body>
<p>您好！欢迎访问麻将测试</p>

<p>步骤</p>
<p>1.用户开麻将房：一个房间只有一个开房间的人，且，一个房间最多4人</p>
<p>2.开房间的人可随时开始游戏，不足人数，由机器人代打</p>
<p>3. a.服务器随机生成牌库 b.服务器生成各个用户手中的牌 c.服务器随机生成谁是庄家</p>
<p>4.用户 抓，出，杠，碰，胡等操作。。。</p>
<p>5.结束</p>

<div id="wsMain">

</div>
<label>你说：</label><input type="text" id="txtMsg"><br>
<input type="button" id="btnSend" value="发送">
</body>

<script type="text/javascript">

    var wsUrl = 'ws://'+'{{ env('SWOOLE_HTTP_HOST', 'chat.zengyanqi.com').':9600' }}' + '?group_num=888888&device_unique_id=QWERTYUI';

    var ws;

    $(function () {
        initWebSocket();

        $('#btnSend').click(function () {
            sendMsg();
        });
    });

    //初始化webSocket
    function initWebSocket() {
        try {
            ws = new WebSocket(wsUrl);
            initEventHandle();
        } catch (ex) {
            outPutMsg('连接服务器失败');
            setTimeout('reConnect()', 5000);
        }
    }

    //webSocket回调事件
    function initEventHandle() {
        //连接监听
        ws.onopen = function (ev) {
            outPutMsg("连接服务器成功");
        };

        //消息监听
        ws.onmessage = function (ev) {
            outPutMsg('服务器说:' + ev.data);
        };

        //关闭监听
        ws.onclose = function (ev) {
            outPutMsg("服务器已关闭");
            //断线重连
            setTimeout('reConnect()', 5000);
        };
    }

    //断线重连
    function reConnect() {
        return false;
        initWebSocket();
    }

    //发送消息
    function sendMsg() {
        var msg = $('#txtMsg').val();

        var data = {'group_num' : '888888', 'device_unique_id': 'QWERTYUI', 'message': msg};
        var ws_message = JSON.stringify(data);

        ws.send(ws_message); //发送消息

        outPutMsg('我说:' + data.toString() + msg);
    }

    //输出消息
    function outPutMsg(msg) {
        var $msg = '<span>' + msg + '</span><br/>';
        $('#wsMain').append($msg);
    }
</script>
