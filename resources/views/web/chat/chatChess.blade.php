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
<p>3. a.服务器随机生成牌库(由房主生成？更简单) b.服务器生成各个用户手中的牌 c.服务器随机生成谁是庄家</p>
<p>4.用户 抓，出，杠，碰，胡等操作。。。</p>
<p>5.结束</p>

<div id="wsMain">
    <label>group_num:</label><input type="text" id="group_num" value="888888"><br>
    <label>join_group_num:</label><input type="text" id="join_group_num" value="888888"><br>
    <label>device_unique_id:</label><input type="text" id="device_unique_id" value="QWERTYUI"><br>
    <input type="button" id="start" value="开始" onclick="start()"><br>
</div>
<label>你说 {-%☋%-} ：</label><input type="text" id="txtMsg"><br>
<input type="button" id="btnSend" value="发送">
</body>

<script type="text/javascript">
    function start()
    {
        initWebSocket();
    }

    var sendSplitStr = '{-%☋%-}';
    var receiveSplitStr = '{-$☋$-}';

    var ws;

    $(function () {

        $('#btnSend').click(function () {
            sendMsg();
        });
    });

    //初始化webSocket
    function initWebSocket() {
        try {
            var wsUrl = 'wss://'+'{{ env('SWOOLE_HTTP_HOST', 'www.zengyanqi.com').':9600' }}'
                +'?device_unique_id='+$('#device_unique_id').val();

            if($('#group_num').val().length>0) {
                wsUrl += '&group_num='+$('#group_num').val()
            } else {
                wsUrl += '&join_group_num='+$('#join_group_num').val()
            }

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
            outPutMsg('服务器说:' );
            outPutReceiveMsg(ev.data);
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


        var ws_message = JSON.stringify(msg);

        ws.send(ws_message); //发送消息

        outPutMsg('我说:' + msg.toString() + msg);
    }

    function outPutMsg(msg) {
        var $msg = '<span>' + msg + '</span><br/>';
        $('#wsMain').append($msg);
    }

    //输出消息
    function outPutReceiveMsg(msg) {
        var jsonMsg = JSON.parse(msg.split(receiveSplitStr)[1]);
        if(msg.split(receiveSplitStr)[0] == 1) {
            var $msg = '<span>' + '(列表：' + '</span>';
            $('#wsMain').append($msg);
        }

        if(msg[1].split(sendSplitStr)[0] == 1) { //获取整套牌
            var $msg = '<span>' + msg[1].split(sendSplitStr)[1] + '</span>';
            $('#wsMain').append($msg);
        } else {
            printValues(jsonMsg);
        }

        if(msg.split(receiveSplitStr)[0] == 1) {
            $('#wsMain').append('<span>' + '列表end)' + '</span><br/>');
        }
    }

    function printValues(obj) {
        for (var k in obj) {
            if (obj[k] instanceof Object) {
                printValues(obj[k]);
            } else {
                var $msg = '<span>' + obj[k] + ' | </span>';
                $('#wsMain').append($msg);
            }
        }
    }
</script>
