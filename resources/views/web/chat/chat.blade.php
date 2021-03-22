<head>
    <meta charset="UTF-8"/>
    <title></title>
    <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="/js/common.js"></script>
    <script type="text/javascript" src="/js/swiper.min.js"></script>
    <script type="text/javascript" src="/js/rich-editor.js"></script>
    <script type="text/javascript" src="/js/upload.js"></script>
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
<input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
<input type="hidden" name="nick_name" value="{{auth("user-auth")->user()->nick_name}}">
<input type="hidden" name="avatar" value="{{auth("user-auth")->user()->avatar}}">
<input type="hidden" name="swoole_http_host" value="{{ env('SWOOLE_HTTP_HOST', 'chat.zengyanqi.com').':9600' }}">

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
            <i btn="lock_screen" title="锁屏" class="icon fa fa-coffee"></i>
            {{--<i btn="setting" title="设置" class="icon fa fa-cog"></i>--}}
        </div>
    </div>
</div>

<div class="chat-emoticon hide">
    <ul><li title="[微笑]"><img src="/img/emoticon/0.gif"></li><li title="[嘻嘻]"><img src="/img/emoticon/1.gif"></li><li title="[哈哈]"><img src="/img/emoticon/2.gif"></li><li title="[可爱]"><img src="/img/emoticon/3.gif"></li><li title="[可怜]"><img src="/img/emoticon/4.gif"></li><li title="[挖鼻]"><img src="/img/emoticon/5.gif"></li><li title="[吃惊]"><img src="/img/emoticon/6.gif"></li><li title="[害羞]"><img src="/img/emoticon/7.gif"></li><li title="[挤眼]"><img src="/img/emoticon/8.gif"></li><li title="[闭嘴]"><img src="/img/emoticon/9.gif"></li><li title="[鄙视]"><img src="/img/emoticon/10.gif"></li><li title="[爱你]"><img src="/img/emoticon/11.gif"></li><li title="[泪]"><img src="/img/emoticon/12.gif"></li><li title="[偷笑]"><img src="/img/emoticon/13.gif"></li><li title="[亲亲]"><img src="/img/emoticon/14.gif"></li><li title="[生病]"><img src="/img/emoticon/15.gif"></li><li title="[太开心]"><img src="/img/emoticon/16.gif"></li><li title="[白眼]"><img src="/img/emoticon/17.gif"></li><li title="[右哼哼]"><img src="/img/emoticon/18.gif"></li><li title="[左哼哼]"><img src="/img/emoticon/19.gif"></li><li title="[嘘]"><img src="/img/emoticon/20.gif"></li><li title="[衰]"><img src="/img/emoticon/21.gif"></li><li title="[委屈]"><img src="/img/emoticon/22.gif"></li><li title="[吐]"><img src="/img/emoticon/23.gif"></li><li title="[哈欠]"><img src="/img/emoticon/24.gif"></li><li title="[抱抱]"><img src="/img/emoticon/25.gif"></li><li title="[怒]"><img src="/img/emoticon/26.gif"></li><li title="[疑问]"><img src="/img/emoticon/27.gif"></li><li title="[馋嘴]"><img src="/img/emoticon/28.gif"></li><li title="[拜拜]"><img src="/img/emoticon/29.gif"></li><li title="[思考]"><img src="/img/emoticon/30.gif"></li><li title="[汗]"><img src="/img/emoticon/31.gif"></li><li title="[困]"><img src="/img/emoticon/32.gif"></li><li title="[睡]"><img src="/img/emoticon/33.gif"></li><li title="[钱]"><img src="/img/emoticon/34.gif"></li><li title="[失望]"><img src="/img/emoticon/35.gif"></li><li title="[酷]"><img src="/img/emoticon/36.gif"></li><li title="[色]"><img src="/img/emoticon/37.gif"></li><li title="[哼]"><img src="/img/emoticon/38.gif"></li><li title="[鼓掌]"><img src="/img/emoticon/39.gif"></li><li title="[晕]"><img src="/img/emoticon/40.gif"></li><li title="[悲伤]"><img src="/img/emoticon/41.gif"></li><li title="[抓狂]"><img src="/img/emoticon/42.gif"></li><li title="[黑线]"><img src="/img/emoticon/43.gif"></li><li title="[阴险]"><img src="/img/emoticon/44.gif"></li><li title="[怒骂]"><img src="/img/emoticon/45.gif"></li><li title="[互粉]"><img src="/img/emoticon/46.gif"></li><li title="[心]"><img src="/img/emoticon/47.gif"></li><li title="[伤心]"><img src="/img/emoticon/48.gif"></li><li title="[猪头]"><img src="/img/emoticon/49.gif"></li><li title="[熊猫]"><img src="/img/emoticon/50.gif"></li><li title="[兔子]"><img src="/img/emoticon/51.gif"></li><li title="[ok]"><img src="/img/emoticon/52.gif"></li><li title="[耶]"><img src="/img/emoticon/53.gif"></li><li title="[good]"><img src="/img/emoticon/54.gif"></li><li title="[NO]"><img src="/img/emoticon/55.gif"></li><li title="[赞]"><img src="/img/emoticon/56.gif"></li><li title="[来]"><img src="/img/emoticon/57.gif"></li><li title="[弱]"><img src="/img/emoticon/58.gif"></li><li title="[草泥马]"><img src="/img/emoticon/59.gif"></li><li title="[神马]"><img src="/img/emoticon/60.gif"></li><li title="[囧]"><img src="/img/emoticon/61.gif"></li><li title="[浮云]"><img src="/img/emoticon/62.gif"></li><li title="[给力]"><img src="/img/emoticon/63.gif"></li><li title="[围观]"><img src="/img/emoticon/64.gif"></li><li title="[威武]"><img src="/img/emoticon/65.gif"></li><li title="[奥特曼]"><img src="/img/emoticon/66.gif"></li><li title="[礼物]"><img src="/img/emoticon/67.gif"></li><li title="[钟]"><img src="/img/emoticon/68.gif"></li><li title="[话筒]"><img src="/img/emoticon/69.gif"></li><li title="[蜡烛]"><img src="/img/emoticon/70.gif"></li><li title="[蛋糕]"><img src="/img/emoticon/71.gif"></li></ul>
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
            {{--<i title="发送文件" class="icon fa fa-folder-o"></i>--}}
            <i title="聊天记录" class="icon icon-log fa fa-clock-o"><span btn="chatLog">聊天记录</span></i>
        </div>
        <div class="dialog-message">
            <div prop="message">
            </div>
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

<!--锁屏-->
<div class="lock-screen hide">
    <div class="mask">
    </div>
    <div class="inner">
        <img src="/img/avatar/a.jpg" />
        <span>曾彦琪</span>
        <div class="un-lock">
            <input txt="unLock" type="text" class="input-sm" placeholder="输入admin解锁" />
            <i class="fa fa-arrow-circle-right" btn="unLock"></i>
        </div>
    </div>
</div>

<div>
    <div class="upload_image_progress hide">
        <div class="progress" style="width: 10%;" progress="0"></div>
    </div>

    <input type="file" name="upload_image_file" class="hide">
</div>

</body>