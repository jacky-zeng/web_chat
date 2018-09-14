<head>
    <meta charset="UTF-8">
    <title>swoole_web_socket 首页</title>
    <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
</head>
<body>
<p>您好！欢迎访问swoole_web_socket</p>
<p>{{ $params }}</p>
<div id="wsMain">

</div>
<label>你说：</label><input type="text" id="txtMsg"><br>
<input type="button" id="btnSend" value="发送">
</body>

<script type="text/javascript">

    var wsUrl = 'ws://118.25.106.248:9600';

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
        initWebSocket();
    }

    //发送消息
    function sendMsg() {
        var msg = $('#txtMsg').val();
        ws.send(msg); //发送消息

        outPutMsg('我说:' + msg);
    }

    //输出消息
    function outPutMsg(msg) {
        var $msg = '<span>' + msg + '</span><br/>';
        $('#wsMain').append($msg);
    }
</script>