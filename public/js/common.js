/**
 * 获取url中的get参数
 * @param name    参数名
 * @param url     需要解析的url 不传表示当前浏览器的url
 * @returns {*}   举例：http://xxx.com?val=666  GetUrlQueryString('val')即可得到666
 * @constructor
 */
function GetUrlQueryString(name, url) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = null;
    if (url) {
        r = ('?' + url.split('?')[1]).substr(1).match(reg);
    } else {
        r = window.location.search.substr(1).match(reg);
    }
    if (r != null) return unescape(r[2]);
    return null;
}

/**
 * 弹出对话框
 */
(function($){
    window.Dialog = window.Dialog || {};

    /* 成功 弹出提示框
     * text :提示文字
     * func :要执行的js
     * has_mask: 是否需要遮照(默认无遮照)
     * time :提示框多久后消失(默认1000ms)
     * */
    window.Dialog.success = function(text,func,has_mask,time){
        if(has_mask){$('body').append('<div id="postMask"></div>');}
        $('body').append('<div id="success" class="alert_a"><div class="qjs_success">'+text+'</div></div>');
        setTimeout(function () {
            if(func){func();}
            $('#success').remove();
            if(has_mask){$('#postMask').remove();}
        }, time?time:1000);
    };

    /* 失败 弹出提示框
     * text :提示文字
     * func :要执行的js
     * has_mask: 是否需要遮照(默认无遮照)
     * time :提示框多久后消失(默认2000ms)
     * */
    window.Dialog.error = function(text,func,has_mask,time){
        if(has_mask){$('body').append('<div id="postMask"></div>');}
        $('body').append('<div id="success" class="alert_a"><div class="qjs_error">'+text+'</div></div>');
        setTimeout(function () {
            if(func){func();}
            $('#success').remove();
            if(has_mask){$('#postMask').remove();}
        }, time?time:2000);
    };

    /* 弹出选择提示框
     * text :提示文字
     * funcOk :选择确定要执行的js
     * funcClose :选择取消或关闭要执行的js
     * */
    window.Dialog.confirm = function(text,funcOk,funcClose){
        var html = '<div class="xcConfirm" id="xcConfirm">' +
            '<div class="xc_layer"></div><div class="popBox">' +
            '<div class="ttBox"><span class="tt">提示</span><button type="button" class="close"><span>×</span></button></div>' +
            '<div class="txtBox"><div class="bigIcon"></div><p>'+text+'</p></div>' +
            '<div class="btnArea"><div class="btnGroup"><a class="sgBtn cancel btn btn-info">取消</a><a class="sgBtn ok btn btn-default">确定</a></div></div></div>' +
            '</div>';
        $('body').append(html);
        $('#xcConfirm').unbind().on('click','.sgBtn.ok',function(){
            if(funcOk){funcOk();}
            $('#xcConfirm').remove();
        }).on('click','.sgBtn.cancel,.ttBox .close',function(){
            if(funcClose){funcClose();}
            $('#xcConfirm').remove();
        });
    };

    /*
     * 弹出框
     * title        标题
     * body         内容
     * has_mask     是否需要遮罩
     * funcOk       点击确定执行的js
     * funcClose    点击取消或关闭执行的js
     * width        弹出框的宽度
     * height       弹出框的高度
     * has_no_footer   弹出框是否不含底部按钮
     */
    window.Dialog.modal = function(title,body,has_mask,funcOk,funcClose,width,height,okTxt,cancelTxt,has_no_footer) {
        if(has_mask){$('body').append('<div id="modalMask"></div>');}
        var html = '<div class="xcModal hide" id="xcModal">' +
            '<div class="popBox">' +
            '<div class="ttBox"><span class="tt">'+ title +'</span><button type="button" class="close"><span>×</span></button></div>' +
            '<div class="txtBox">' + body + '</div>' +
            '<div class="btnArea '+(has_no_footer?"hide":"")+'"><div class="btnGroup"><a class="sgBtn cancel btn btn-info">'+(cancelTxt?cancelTxt:"取消")+'</a><a class="sgBtn ok btn btn-default">'+(okTxt?okTxt:"确定")+'</a></div></div></div>' +
            '</div>';
        $('body').append(html);
        if(width) {
            $('#xcModal').find('.popBox').css({'width': width + 'px', 'margin-left': '-' + width / 2 + 'px'});
        }
        if(height) {
            $('#xcModal').find('.popBox').css({'height': height + 'px', 'margin-top': '-' + height / 2 + 'px'});
        }
        $('#xcModal').removeClass('hide');
        $('#xcModal').unbind().on('click', '.sgBtn.ok', function () {
            var rs = false;
            if (funcOk) {
                rs = funcOk($(this).parents('#xcModal')); //回调函数含当前弹出层的对象，可进行数据传递
            }
            if(!rs){ //默认点击确定后，关闭弹出层
                if (funcClose) {
                    var handel = setInterval(function () {
                        if($('#postMask').length == 0){
                            clearInterval(handel);
                            funcClose();
                        }
                    },500);
                }
                if(has_mask){$('#modalMask').remove();}
                $('#xcModal').remove();
            }
        }).on('click', '.sgBtn.cancel,.ttBox .close', function () {
            if (funcClose) {
                var handel = setInterval(function () {
                    if($('#postMask').length == 0){
                        clearInterval(handel);
                        funcClose();
                    }
                },500);
            }
            if(has_mask){$('#modalMask').remove();}
            $('#xcModal').remove();
        });

        $('#xcModal').find('.popBox').css('position', 'absolute'); //变absolute后 才可拖动
        var ttBox = $('#xcModal').find('.ttBox')[0];
        var xcModal = $('#xcModal')[0];
        //变为可拖动
        ttBox.onmousedown = function (ev) {
            var oevent = ev || event;
            var distanceX = oevent.clientX - xcModal.offsetLeft;
            var distanceY = oevent.clientY - xcModal.offsetTop;

            document.onmousemove = function (ev) {
                var oevent = ev || event;
                xcModal.style.left = oevent.clientX - distanceX + 'px';
                xcModal.style.top = oevent.clientY - distanceY + 'px';
            };
            document.onmouseup = function () {
                document.onmousemove = null;
                document.onmouseup = null;
            };
        }
    };

})(jQuery);