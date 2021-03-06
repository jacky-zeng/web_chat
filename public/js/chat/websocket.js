var wsUrl;
var ws;

$(function () {
    wsUrl = 'ws://'+$('[name="swoole_http_host"]').val()+'?user_id=' + $('[name="user_id"]').val() + '&token=' + $('[name="token"]').val();
    initWebSocket();
});

//初始化webSocket
function initWebSocket() {
    try {
        ws = new WebSocket(wsUrl);
        initEventHandle();
    } catch (ex) {
        Dialog.error('连接服务器失败', false, true);
        setTimeout('reConnect()', 5000);
    }
}

//webSocket回调事件
function initEventHandle() {
    //连接监听
    ws.onopen = function (ev) {
        Dialog.success('连接服务器成功');
    };

    //消息监听
    ws.onmessage = function (ev) {
        var msgs = ev.data.split('{-$☋$-}');  //对应App\Models\WebSocket\里的分隔符
        var type = parseInt(msgs[0]);         //对应App\Models\WebSocket\里的会话类型
        switch (type) {
            case 1:
                initUserList(msgs[1]);   //初始化聊天用户列表
                break;
            case 2:
                getMsg(msgs[1]);         //用户收到聊天信息
                break;
            case 3:
                updateUserList(msgs[1]); //刷新聊天用户列表
                break;
            case 4:
                delUserList(msgs[1]);    //用户下线,删除用户列表中该用户
                break;
            default:
                break;
        }
    };

    //关闭监听
    ws.onclose = function (ev) {
        Dialog.error('服务器已关闭', false, true);
        //断线重连
        setTimeout('reConnect()', 5000);
    };
}

//断线重连
function reConnect() {
    initWebSocket();
}

//初始化用户列表
function initUserList(data) {
    //console.log('initUserList:' + data);
    data = $.parseJSON(data);
    var $user_li = '';
    $.each(data, function (key, item) { //机器人
        if ($('[name="user_id"]').val() == key) {
            $user_li += '<li user_id="' + key + '" avatar="' + '/img/avatar/robot.jpg' + '" nick_name="' + '机器人笨笨' + '">'
                + '<img src="' + '/img/avatar/robot.jpg' + '" class="member-image"/>'
                + '<span>' + '机器人笨笨' + '</span><span class="span-online"><i class="fa fa-circle"></i>&nbsp;在线</span></li>';
        }
    });
    $.each(data, function (key, item) {
        if ($('[name="user_id"]').val() != key && item['is_online']) {  //在线用户
            var $online_status_span = '<span class="span-online"><i class="fa fa-circle"></i>&nbsp;在线</span></li>';
            $user_li += '<li user_id="' + key + '" avatar="' + item['avatar'] + '" nick_name="' + item['nick_name'] + '">'
                + '<img src="' + item['avatar'] + '" class="member-image '+(item['is_online']?'':'member-not-online')+'"/>'
                + '<span>' + item['nick_name'] + '</span>' + $online_status_span + '</li>';
        }
    });
    $.each(data, function (key, item) {
        if ($('[name="user_id"]').val() != key && !item['is_online']) { //离线用户
            var $online_status_span = '<span class="span-not-online"><i class="fa fa-circle"></i>&nbsp;离线</span></li>';
            $user_li += '<li user_id="' + key + '" avatar="' + item['avatar'] + '" nick_name="' + item['nick_name'] + '">'
                + '<img src="' + item['avatar'] + '" class="member-image '+(item['is_online']?'':'member-not-online')+'"/>'
                + '<span>' + item['nick_name'] + '</span>' + $online_status_span + '</li>';
        }
    });
    $('[prop="tab_user"]').find('ul').html('').append($user_li);
}

//更新用户列表
function updateUserList(data) {
    //console.log('updateUserList:' + data);
    data = $.parseJSON(data);
    var user_id = 0;
    var avatar = 0;
    var nick_name = 0;
    //其实这是一个一维数组
    $.each(data, function (key, item) {
        user_id = key;
        avatar = item['avatar'];
        nick_name = item['nick_name'];
    });
    if ($('[name="user_id"]').val() != user_id) {
        var $this_li = null;
        var has = false;
        $('[prop="tab_user"]').find('li').each(function () {
            if ($(this).attr('user_id') == user_id) {
                $(this).find('img').attr('src', avatar);
                $(this).find('span').text(nick_name);
                $this_li = $(this);
                has = true;
            }
        });
        if (!has) { //之前用户列表中不存在该用户 则将该上线用户放在最后一个离线用户前面
            var $online_status_span = '<span class="span-online"><i class="fa fa-circle"></i>&nbsp;在线</span></li>';
            var $user_li = '<li user_id="' + user_id + '" avatar="' + avatar + '" nick_name="' + nick_name + '">'
                + '<img src="' + avatar + '" class="member-image"/>'
                + '<span>' + nick_name + '</span>' + $online_status_span + '</li>';

            var $last_not_online_li = null;
            $('[prop="tab_user"]').find('li').each(function () {
                if($(this).find('.member-not-online').length) {
                    $last_not_online_li = $(this);
                }
            });
            if($last_not_online_li) {
                $last_not_online_li.before($user_li);
            } else {
                $('[prop="tab_user"]').find('ul').append($user_li);
            }
        } else { //上线用户移动到离线用户前面
            $this_li.find('img').removeClass('member-not-online');
            $this_li.find('.span-not-online').html('<i class="fa fa-circle"></i>&nbsp;在线').removeClass('span-not-online').addClass('span-online');
            var $last_not_online_li = null;
            $('[prop="tab_user"]').find('li').each(function () {
                if($(this).find('.member-not-online').length) {
                    $last_not_online_li = $(this);
                    return false;
                }
            });
            if($last_not_online_li) {
                $last_not_online_li.before($this_li[0].outerHTML);
                $this_li.remove();
            }
        }
    }
}

//退出登录的用户变离线
function delUserList(data) {
    //console.log('delUserList:' + data);
    data = $.parseJSON(data);
    var user_id = 0;
    $.each(data, function (key, item) {
        user_id = item;
    });
    var $tab_user_li = $('[prop="tab_user"]').find('[user_id="' + user_id + '"]');
    if($tab_user_li.length) {
        $tab_user_li.find('img').addClass('member-not-online');
        $tab_user_li.find('.span-online').addClass('span-not-online').removeClass('span-online');
        //离线用户放到列表最后面
        $('[prop="tab_user"]').find('ul').append($tab_user_li[0].outerHTML.replace('在线', '离线'));
        $tab_user_li.remove();
    }
}

//用户收到聊天信息
function getMsg(data) {
    //console.log('getMsg:' + data);
    data = $.parseJSON(data);
    var from_user_id = data['from_user_id'];
    var date = data['date'];
    var message = data['message'];

    var $li_from_user = $('[prop="tab_user"]').find('li[user_id="' + from_user_id + '"]');
    if ($li_from_user.length) {
        //步骤一 初始化聊天对话框
        if (from_user_id == $('[name="user_id"]').val() && false) {
            Dialog.error('请勿跟自己聊天', false, true);
            return false;
        } else if ($('.chat-bottom').find('li[user_id="' + from_user_id + '"]').length) { //存在底部小tab

        } else {
            var $li = '<li class="swiper-slide" user_id="' + from_user_id + '">' +
                '           <a href="#">' + $li_from_user.attr('nick_name') + '</a><span prop="close">×</span>' +
                '       </li>';
            $('[prop="chat-bottom"]').find('ul').append($li);
        }
        if ($('.chat-dialog[user_id="' + from_user_id + '"]').length) { //存在聊天对话框
            if ($('.chat-dialog[user_id="' + from_user_id + '"]').hasClass('hide')) { //聊天对话框是隐藏的
                $('.chat-bottom').find('li[user_id="' + from_user_id + '"]').addClass('message_not_read');
            }
        } else {
            $('.chat-bottom').find('li[user_id="' + from_user_id + '"]').addClass('message_not_read');
            var $chat_dialog_template = $('[prop="chat-dialog-template"]').clone().removeAttr('prop');
            $chat_dialog_template.attr('user_id', from_user_id);
            $chat_dialog_template.find('[prop="avatar"]').attr('src', $li_from_user.attr('avatar'));
            $chat_dialog_template.find('[prop="nick_name"]').text($li_from_user.attr('nick_name'));
            $('body').append($chat_dialog_template);
            var $dialog_content = $('.chat-dialog[user_id="' + from_user_id + '"]').find('.dialog-content');
            setTimeout(function () {
                $dialog_content.scrollTop($dialog_content[0].scrollHeight);
            }, 500);
            initChatDialog($chat_dialog_template);
        }

        //步骤二 加入聊天信息
        //填充聊天模板
        var $dialog_chat_user_template = $('[prop="dialog-chat-user-template"]').clone().removeAttr('prop').removeClass('hide');
        $dialog_chat_user_template.find('[prop="user_time"]').text(date);
        $dialog_chat_user_template.find('[prop="user_nick_name"]').text($li_from_user.attr('nick_name'));
        $dialog_chat_user_template.find('[prop="user_avatar"]').attr('src', $li_from_user.attr('avatar'));
        $dialog_chat_user_template.find('[prop="user_msg"]').html(message);
        //加入到聊天对话框
        var $current_chat_dialog = $('.chat-dialog[user_id="' + from_user_id + '"]');
        $current_chat_dialog.find('ul').append($dialog_chat_user_template);
        setTimeout(function () {
            $current_chat_dialog.find('.dialog-content').scrollTop($current_chat_dialog.find('.dialog-content')[0].scrollHeight);
        }, 500);
    }
}