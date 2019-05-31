$(function () {
    init();
    //聊天主面板 显示/隐藏
    $('.min-content').click(function () {
        $('.chat-min,.chat-box').toggleClass('hide');
    });
    //关闭聊天主面板
    $('.chat-box').on('click', '.close', function () {
        $('.chat-min,.chat-box').toggleClass('hide');
    });
    //打开聊天对话框
    $('.box-content [prop="tab_user"]').on('click', 'ul li', function () {
        if ($(this).attr('user_id') == $('[name="user_id"]').val() && false) {
            Dialog.error('请勿跟自己聊天', false, true);
            return false;
        } else if ($('.chat-bottom').find('li[user_id="' + $(this).attr('user_id') + '"]').length) { //存在底部小tab
            $('.chat-bottom').find('li[user_id="' + $(this).attr('user_id') + '"]').removeClass('message_not_read');
        } else {
            var $li = '<li class="swiper-slide" user_id="' + $(this).attr('user_id') + '">' +
                '           <a href="#">' + $(this).attr('nick_name') + '</a><span prop="close">×</span>' +
                '       </li>';
            $('[prop="chat-bottom"]').find('ul').append($li);
        }

        if ($('.chat-dialog[user_id="' + $(this).attr('user_id') + '"]').length) {
            $('.chat-dialog').css('z-index', '1');
            $('.chat-dialog[user_id="' + $(this).attr('user_id') + '"]').css('z-index', '999').removeClass('hide');
        } else {
            var $chat_dialog_template = $('[prop="chat-dialog-template"]').clone().removeAttr('prop').removeClass('hide');
            $chat_dialog_template.attr('user_id', $(this).attr('user_id'));
            $chat_dialog_template.find('[prop="avatar"]').attr('src', $(this).attr('avatar'));
            $chat_dialog_template.find('[prop="nick_name"]').text($(this).attr('nick_name'));
            $('body').append($chat_dialog_template);
            var $dialog_content = $('.chat-dialog[user_id="' + $(this).attr('user_id') + '"]').find('.dialog-content');
            $dialog_content.scrollTop($dialog_content[0].scrollHeight);
            initChatDialog($chat_dialog_template);
        }
    });
    //主面板 单聊/群组/聊天室 tab切换
    $('.box-tab').on('click', 'div', function () {
        if (!$(this).hasClass('active')) {
            $(this).addClass('active').siblings().removeClass('active');
            var prop = $(this).attr('prop');
            $('.box-content').find('[prop="' + prop + '"]').removeClass('hide').addClass('active')
                .siblings().addClass('hide').removeClass('active');
        }
    });
    //点击底部聊天小tab 切换 聊天
    $('[prop="chat-bottom"]').on('click', 'li', function () {
        var $current_chat_dialog = $('.chat-dialog[user_id="' + $(this).attr('user_id') + '"]');
        if ($current_chat_dialog.hasClass('hide')) {
            $(this).removeClass('message_not_read');
            $current_chat_dialog.removeClass('hide');
        }
        $('.chat-dialog').css('z-index', '1');
        $current_chat_dialog.css('z-index', '999');
    });
    //关闭底部聊天小tab 同时关闭聊天
    $('[prop="chat-bottom"]').on('click', '[prop="close"]', function () {
        $('.chat-dialog[user_id="' + $(this).parent('li').attr('user_id') + '"]').addClass('hide');
        $(this).parent('li').remove();
        return false; //阻止冒泡
    });
    //退出登录
    $('[btn="logout"]').click(function () {
        Dialog.confirm('确定退出?', function () {
            window.top.location.href = '/logout'
        });
    });

    //锁屏
    $('[btn="lock_screen"]').click(function () {
        localStorage.setItem('is_locked', '1');
        $('.lock-screen').removeClass('hide');
    });

    //解除锁屏
    $('[btn="unLock"]').unbind().click(function () {
        if($('[txt="unLock"]').val() == 'admin') {
            localStorage.setItem('is_locked', '0');
            $('.lock-screen').addClass('hide');
        } else {
            Dialog.error('密码错误', false, true);
        }
    });

    //设置
    $('[btn="setting"]').click(function () {
        Dialog.error('功能开发中');
    });
});

//初始化
function init() {
    //是否锁屏
    if(localStorage.getItem('is_locked') == '1') {
        $('.lock-screen').removeClass('hide');
    }
    /*底部任务栏小聊天tab*/
    var swiper_tab = new Swiper('.swiper-container-tab', {
        slidesPerView: 'auto'
    });
    /*面板变可拖动*/
    $('#chat-box').find('.main-box').css('position', 'absolute'); //变absolute后 才可拖动
    var chatHead = $('#chat-box').find('.box-head')[0];
    var chatBox = $('#chat-box')[0];
    chatHead.onmousedown = function (ev) {
        //拖动
        var oevent = ev || event;
        var distanceX = oevent.clientX - chatBox.offsetLeft;
        var distanceY = oevent.clientY - chatBox.offsetTop;

        document.onmousemove = function (ev) {
            var oevent = ev || event;
            chatBox.style.left = oevent.clientX - distanceX + 'px';
            chatBox.style.top = oevent.clientY - distanceY + 'px';
        };
        document.onmouseup = function () {
            document.onmousemove = null;
            document.onmouseup = null;
        };
    };
    //对话框置顶
    $('#chat-box').click(function () {
        $('#chat-box').css('z-index', '1000');
    });
}

//初始化聊天对话框
function initChatDialog($chat_dialog_template) {
    //步骤一：对话框置顶
    $('.chat-dialog').css('z-index', '1');
    $chat_dialog_template.css('z-index', '999');

    new RichEditor($chat_dialog_template.find('[prop="message"]')[0]); //html编辑器

    /*聊天对话框变可拖动*/
    $chat_dialog_template.find('.main-dialog').css('position', 'absolute'); //变absolute后 才可拖动
    var dialogHead = $chat_dialog_template.find('.dialog-head')[0];
    var dialogBox = $chat_dialog_template[0];
    dialogHead.onmousedown = function (ev) {
        //可拖动
        var oevent = ev || event;
        var distanceX = oevent.clientX - dialogBox.offsetLeft;
        var distanceY = oevent.clientY - dialogBox.offsetTop;

        document.onmousemove = function (ev) {
            var oevent = ev || event;
            dialogBox.style.left = oevent.clientX - distanceX + 'px';
            dialogBox.style.top = oevent.clientY - distanceY + 'px';
        };
        document.onmouseup = function () {
            document.onmousemove = null;
            document.onmouseup = null;
        };
    };

    //对话框被点击时置顶
    $chat_dialog_template.on('click', '.main-dialog', function () {
        $('#chat-box').css('z-index', '1');
        $('.chat-dialog').css('z-index', '1');
        $chat_dialog_template.css('z-index', '999');
    });

    //选择表情
    $chat_dialog_template.on('click', '.dialog-tool .fa-smile-o', function () {
        //显示所有可选表情
        $('.chat-emoticon').css({
            'left' : $chat_dialog_template.position().left - 335,
            'top' : $chat_dialog_template.position().top - 110,
            'z-index':'1000'
        }).removeClass('hide');
        //选择表情
        $('.chat-emoticon').find('li').unbind().click(function () {
            //表情插入文字后方
            if($chat_dialog_template.find('[prop="message"]').find('iframe').contents().find('body').find('div').length) {
                $chat_dialog_template.find('[prop="message"]').find('iframe').contents().find('body').find('div').last().append($(this).html());
            } else {
                $chat_dialog_template.find('[prop="message"]').find('iframe').contents().find('body').append($(this).html());
            }
        });
        //隐藏表情选择
        $('body').unbind().click(function (e) {
            if(!$(e.target).hasClass('fa-smile-o')) {
                $('.chat-emoticon').addClass('hide');
                $('body').unbind();
            }
        });
    });

    $chat_dialog_template.on('click', '.dialog-tool .fa-image, .dialog-tool .fa-folder-o', function () {
        Dialog.error('功能开发中');
    });

    //发送按钮 发送信息
    $chat_dialog_template.on('click', '[btn="send"]', function () {
        sendMessage($(this).parents('.chat-dialog').attr('user_id'));
    });
    //聊天记录
    $chat_dialog_template.on('click', '[btn="chatLog"]', function () {
        var $this = $(this);
        setTimeout(function () {
            showChatLog($this);
        }, 100);
    });
    //关闭聊天对话框面板
    $chat_dialog_template.on('click', '[btn="close"]', function () {
        var user_id = $(this).parents('.chat-dialog').attr('user_id');
        $('.chat-dialog[user_id="' + user_id + '"]').addClass('hide');
    });
}

//初始化聊天记录对话框
function initChatLogDialog($chat_log_dialog_template) {
    //步骤一：对话框置顶
    $('.chat-dialog').css('z-index', '1'); //其他对话框置底
    $chat_log_dialog_template.css('z-index', '999');
    /*聊天对话框变可拖动*/
    $chat_log_dialog_template.find('.main-dialog').css('position', 'absolute'); //变absolute后 才可拖动
    var dialogHead = $chat_log_dialog_template.find('.dialog-head')[0];
    var dialogBox = $chat_log_dialog_template[0];
    dialogHead.onmousedown = function (ev) {
        //可拖动
        var oevent = ev || event;
        var distanceX = oevent.clientX - dialogBox.offsetLeft;
        var distanceY = oevent.clientY - dialogBox.offsetTop;

        document.onmousemove = function (ev) {
            var oevent = ev || event;
            dialogBox.style.left = oevent.clientX - distanceX + 'px';
            dialogBox.style.top = oevent.clientY - distanceY + 'px';
        };
        document.onmouseup = function () {
            document.onmousemove = null;
            document.onmouseup = null;
        };
    };

    //对话框被点击时置顶
    $chat_log_dialog_template.on('click', '.main-dialog', function () {
        $('#chat-box').css('z-index', '1');
        $('.chat-dialog').css('z-index', '1');
        $chat_log_dialog_template.css('z-index', '999');
    });
    //关闭聊天对话框面板
    $chat_log_dialog_template.on('click', '[btn="close"]', function () {
        var user_id = $(this).parents('.chat-log-dialog').attr('user_id');
        $('.chat-log-dialog[user_id="' + user_id + '"]').remove();
    });
    //获取聊天记录

    $.ajax({
        type: "get",
        url: "/chat_log",
        data: 'user_id=' + $chat_log_dialog_template.attr('user_id'),
        dataType: "json",
        success: function (e) {
            if(e.code == 200) {
                var user_id = $('[name="user_id"]').val();
                console.log(e);
                for(var i = 0; i< e.data.length; i++) {
                    var to_user_id = e.data[i].user_id;
                    var message = e.data[i].message;
                    var is_machine = e.data[i].is_machine;
                    var created_at = e.data[i].created_at;

                    if(is_machine || (user_id != to_user_id)) {
                        var $tab_user_li = $('[prop="tab_user"]').find('li[user_id="' + to_user_id + '"]');
                        //填充聊天模板
                        var $dialog_chat_user_template = $('[prop="dialog-chat-user-template"]').clone().removeAttr('prop').removeClass('hide');
                        $dialog_chat_user_template.find('[prop="user_time"]').text(created_at);
                        $dialog_chat_user_template.find('[prop="user_nick_name"]').text($tab_user_li.attr('nick_name'));
                        $dialog_chat_user_template.find('[prop="user_avatar"]').attr('src', $tab_user_li.attr('avatar'));
                        $dialog_chat_user_template.find('[prop="user_msg"]').html(message);
                        $chat_log_dialog_template.find('.dialog-log-content').find('ul').append($dialog_chat_user_template);
                    } else {
                        //填充聊天模板
                        var $dialog_chat_mine_template = $('[prop="dialog-chat-mine-template"]').clone().removeAttr('prop').removeClass('hide');
                        $dialog_chat_mine_template.find('[prop="mine_time"]').text(created_at);
                        $dialog_chat_mine_template.find('[prop="mine_nick_name"]').text($('[name="nick_name"]').val());
                        $dialog_chat_mine_template.find('[prop="mine_avatar"]').attr('src', $('[name="avatar"]').val());
                        $dialog_chat_mine_template.find('[prop="mine_msg"]').html(message);
                        $chat_log_dialog_template.find('.dialog-log-content').find('ul').append($dialog_chat_mine_template);
                    }
                }
                var $dialog_log_content = $chat_log_dialog_template.find('.dialog-log-content');
                $dialog_log_content.scrollTop($dialog_log_content[0].scrollHeight);
            } else {
                Dialog.error(e.message, false, true);
            }
        }
    });
}

//发送信息 to_user_id:接受方的用户id
function sendMessage(to_user_id) {
    //获取聊天信息
    var $current_chat_dialog = $('.chat-dialog[user_id="' + to_user_id + '"]');
    var message = $current_chat_dialog.find('[prop="message"]').find('iframe').contents().find('body').html();

    if (message.toString().trim() == '') {
        return false;
    } else if(message.toString().length >= 5000) {
        Dialog.error('聊天内容过长', false, true);
        return false;
    }
    //填充聊天模板
    var $dialog_chat_mine_template = $('[prop="dialog-chat-mine-template"]').clone().removeAttr('prop').removeClass('hide');
    var date = new Date(+new Date() + 8 * 3600 * 1000).toISOString().replace(/T/g, ' ').replace(/\.[\d]{3}Z/, '');
    $dialog_chat_mine_template.find('[prop="mine_time"]').text(date);
    $dialog_chat_mine_template.find('[prop="mine_nick_name"]').text($('[name="nick_name"]').val());
    $dialog_chat_mine_template.find('[prop="mine_avatar"]').attr('src', $('[name="avatar"]').val());
    $dialog_chat_mine_template.find('[prop="mine_msg"]').html(message);
    //加入到聊天对话框
    $current_chat_dialog.find('ul').append($dialog_chat_mine_template);
    $current_chat_dialog.find('.dialog-content').scrollTop($current_chat_dialog.find('.dialog-content')[0].scrollHeight);
    $current_chat_dialog.find('[prop="message"]').find('iframe').contents().find('body').html(''); //清空打字框
    //websocket发送聊天信息 （对方user_id拼上聊天的内容）
    var data = {'from_user_id': $('[name="user_id"]').val(), 'to_user_id': to_user_id, 'message': message};
    var ws_message = JSON.stringify(data);
    ws.send(ws_message); //发送消息
}

//聊天记录
function showChatLog($obj) {
    var $chat_dialog = $obj.parents('.chat-dialog');
    if ($('.chat-log-dialog[user_id="' + $chat_dialog.attr('user_id') + '"]').length) {
        $('.chat-dialog').css('z-index', '1'); //其他对话框置底
        $('.chat-log-dialog[user_id="' + $chat_dialog.attr('user_id') + '"]').css('z-index', '999').removeClass('hide');
    } else {
        var $chat_log_dialog_template = $('[prop="chat-log-dialog-template"]').clone().removeAttr('prop').removeClass('hide');
        $chat_log_dialog_template.attr('user_id', $chat_dialog.attr('user_id'));
        $chat_log_dialog_template.find('[prop="nick_name"]').text($chat_dialog.find('[prop="nick_name"]').text());
        $('body').append($chat_log_dialog_template);
        initChatLogDialog($chat_log_dialog_template);
    }
}