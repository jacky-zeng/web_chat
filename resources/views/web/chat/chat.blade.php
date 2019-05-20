<head>
    <meta charset="UTF-8"/>
    <title></title>
    <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="/js/common.js"></script>
    <script type="text/javascript" src="/js/swiper.min.js"></script>
    <script type="text/javascript" src="/js/chat/chat.js"></script>
    <script type="text/javascript" src="/js/chat/websocket.js"></script>
    <link rel="stylesheet" href="/css/font-awesome.min.css">
    <link rel="stylesheet" href="/css/chat.css">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/swiper.min.css">
</head>

<body>

<input type="hidden" name="user_id" value="{{ \App\Util\EnDecryption::encrypt(auth("user-auth")->user()->id) }}">
<input type="hidden" name="token" value="{{ $token }}">
<input type="hidden" name="nick_name" value="{{auth("user-auth")->user()->nick_name}}">
<input type="hidden" name="avatar" value="{{auth("user-auth")->user()->avatar}}">
<input type="hidden" name="swoole_http_host" value="{{ env('SWOOLE_HTTP_HOST', 'chat.zengyanqi.com:9600') }}">

<!--贴边小面板-->
<div class="chat-min hide">
    <div class="min-content" title="展开聊天"><i class="arrow fa fa-angle-right"></i></div>
</div>

<!--聊天主面板-->
<div class="chat-box" id="chat-box">
    <div class="main-box">
        <div class="box-head">
            <img src="{{auth("user-auth")->user()->avatar}}" class="user-image"/>
            <span class="close"><span>×</span></span>
        </div>
        <div class="box-tab">
            <div prop="tab_user" title="联系人" class="tab active"><i class="fa fa-user"></i></div>
            <div prop="tab_group" title="群组" class="tab"><i class="fa fa-users"></i></div>
            <div prop="tab_chat" title="聊天室" class="tab"><i class="fa fa-twitch"></i></div>
        </div>
        <div class="box-content">
            <div prop="tab_user" class="active">
                <ul>
                    {{--<li><img src="/img/avatar/haijiaoluoluo.jpg"--}}
                             {{--class="member-image"/><span>海角诺诺</span></li>--}}
                </ul>
            </div>
            <div prop="tab_group" class="hide">群组开发中</div>
            <div prop="tab_chat" class="hide">聊天室开发中</div>
        </div>
        <div class="box-footer">
            <i btn="logout" style="color: red;" title="退出登录" class="icon fa fa-power-off"></i>
            <i title="锁屏" class="icon fa fa-coffee"></i>
            <i title="设置" class="icon fa fa-cog"></i>
        </div>
    </div>
</div>

<!--对话框模板整体-->
<div class="chat-dialog hide" prop="chat-dialog-template">
    <div class="main-dialog">
        <div class="dialog-head">
            <img class="member-image" prop="avatar" src="{{--/img/avatar/haijiaoluoluo.jpg--}}" />
            <span class="member-name" prop="nick_name">{{--海角诺诺--}}</span>
            <span class="close" btn="close"><span>×</span></span>
        </div>
        <div class="dialog-content">
            <ul>
                {{--<li class="dialog-chat-mine">--}}
                    {{--<div class="dialog-chat-user">--}}
                        {{--<cite><i prop="mine_time">2018-09-17 13:55:03</i><span prop="mine_nick_name">redsun</span></cite>--}}
                        {{--<img prop="mine_avatar" src="/img/avatar/redsun.gif">--}}
                    {{--</div>--}}
                    {{--<div class="dialog-chat-text">--}}
                        {{--<div class="dialog-chat-triangle"></div>--}}
                        {{--<div class="dialog-chat-message" prop="mine_msg">hi! 本周的任务整的咋样了</div>--}}
                    {{--</div>--}}
                {{--</li>--}}
                {{--<li>--}}
                    {{--<div class="dialog-chat-user">--}}
                        {{--<img prop="user_avatar" src="/img/avatar/haijiaoluoluo.jpg">--}}
                        {{--<cite><span prop="user_nick_name">海角诺诺</span><i prop="user_time">2018-09-17 13:55:03</i></cite>--}}
                    {{--</div>--}}
                    {{--<div class="dialog-chat-text">--}}
                        {{--<div class="dialog-chat-triangle"></div>--}}
                        {{--<div class="dialog-chat-message" prop="user_msg">快好了，你看看这个样式做的还可以吧?</div>--}}
                    {{--</div>--}}
                {{--</li>--}}
            </ul>
        </div>
        <div class="dialog-tool">
            <i title="选择表情" class="icon fa fa-smile-o"></i>
            <i title="发送图片" class="icon fa fa-image"></i>
            <i title="发送文件" class="icon fa fa-folder-o"></i>
            <i title="聊天记录" class="icon icon-log fa fa-clock-o"><span btn="chatLog">聊天记录</span></i>
        </div>
        <div class="dialog-message">
            <textarea></textarea>
        </div>
        <div class="dialog-footer">
            <span btn="send">发送</span>
            <span btn="close">关闭</span>
        </div>
    </div>
</div>

<!--聊天记录模板整体-->
<div class="chat-dialog chat-log-dialog hide" prop="chat-log-dialog-template">
    <div class="main-dialog">
        <div class="dialog-head">
            &nbsp;&nbsp;&nbsp;&nbsp;与<span prop="nick_name">{{--海角诺诺--}}</span>的聊天记录(三天内)
            <span class="close" btn="close"><span>×</span></span>
        </div>
        <div class="dialog-content dialog-log-content">
            <ul>
            </ul>
        </div>
    </div>
</div>

<!--底部任务栏小聊天tab-->
<div class="swiper-container swiper-container-tab chat-bottom" prop="chat-bottom">
    <ul class="swiper-wrapper">
        {{--<li class="swiper-slide">--}}
            {{--<a href="#">111</a><span prop="close">×</span>--}}
        {{--</li>--}}
    </ul>
</div>

<!--聊天模板-->
<!--自己说话-->
<li class="dialog-chat-mine hide" prop="dialog-chat-mine-template">
    <div class="dialog-chat-user">
        <cite><i prop="mine_time"></i><span prop="mine_nick_name"></span></cite>
        <img prop="mine_avatar" src="">
    </div>
    <div class="dialog-chat-text">
        <div class="dialog-chat-triangle"></div>
        <div class="dialog-chat-message" prop="mine_msg"></div>
    </div>
</li>
<!--对方说话-->
<li class="hide" prop="dialog-chat-user-template">
    <div class="dialog-chat-user">
        <img prop="user_avatar" src="">
        <cite><span prop="user_nick_name"></span><i prop="user_time"></i></cite>
    </div>
    <div class="dialog-chat-text">
        <div class="dialog-chat-triangle"></div>
        <div class="dialog-chat-message" prop="user_msg"></div>
    </div>
</li>

</body>
