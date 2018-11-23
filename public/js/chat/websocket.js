var wsUrl;
var ws;

$(function () {
    wsUrl = 'ws://118.25.106.248:9600?user_id=' + $('[name="user_id"]').val() + '&token=' + $('[name="token"]').val();
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
    $.each(data, function (key, item) {
        if ($('[name="user_id"]').val() == key) {
            $user_li += '<li user_id="' + key + '" avatar="' + '/img/avatar/robot.jpg' + '" nick_name="' + '机器人笨笨' + '">'
                + '<img src="' + '/img/avatar/robot.jpg' + '" class="member-image"/>'
                + '<span>' + '机器人笨笨' + '</span></li>';
        }
    });
    $.each(data, function (key, item) {
        if ($('[name="user_id"]').val() != key) {
            $user_li += '<li user_id="' + key + '" avatar="' + item['avatar'] + '" nick_name="' + item['nick_name'] + '">'
                + '<img src="' + item['avatar'] + '" class="member-image"/>'
                + '<span>' + item['nick_name'] + '</span></li>';
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
    //其实这是一个一纬数组
    $.each(data, function (key, item) {
        user_id = key;
        avatar = item['avatar'];
        nick_name = item['nick_name'];
    });
    if ($('[name="user_id"]').val() != user_id) {
        var has = false; //之前用户列表中是否存在该用户
        $('[prop="tab_user"]').find('li').each(function () {
            if ($(this).attr('user_id') == user_id) {
                $(this).find('img').attr('src', avatar);
                $(this).find('span').text(nick_name);
                has = true;
            }
        });
        if (!has) {
            var $user_li = '<li user_id="' + user_id + '" avatar="' + avatar + '" nick_name="' + nick_name + '">'
                + '<img src="' + avatar + '" class="member-image"/>'
                + '<span>' + nick_name + '</span></li>';
            $('[prop="tab_user"]').find('ul').append($user_li);
        }
    }
}

//删除退出登录的用户
function delUserList(data) {
    //console.log('delUserList:' + data);
    data = $.parseJSON(data);
    var user_id = 0;
    $.each(data, function (key, item) {
        user_id = item;
    });
    $('[prop="tab_user"]').find('[user_id="' + user_id + '"]').remove();
}

//用户收到聊天信息
function getMsg(data) {
    //console.log('getMsg:' + data);
    data = $.parseJSON(data);
    var from_user_id = data['from_user_id'];
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
            $dialog_content.scrollTop($dialog_content[0].scrollHeight);
            initChatDialog($chat_dialog_template);
        }

        //步骤二 加入聊天信息
        //填充聊天模板
        var $dialog_chat_user_template = $('[prop="dialog-chat-user-template"]').clone().removeAttr('prop').removeClass('hide');
        $dialog_chat_user_template.find('[prop="user_time"]').text('2018-10-11 11:45:08');
        $dialog_chat_user_template.find('[prop="user_nick_name"]').text($li_from_user.attr('nick_name'));
        $dialog_chat_user_template.find('[prop="user_avatar"]').attr('src', $li_from_user.attr('avatar'));
        $dialog_chat_user_template.find('[prop="user_msg"]').html(message);
        //加入到聊天对话框
        var $current_chat_dialog = $('.chat-dialog[user_id="' + from_user_id + '"]');
        $current_chat_dialog.find('ul').append($dialog_chat_user_template);
        $current_chat_dialog.find('.dialog-content').scrollTop($current_chat_dialog.find('.dialog-content')[0].scrollHeight);
    }
}