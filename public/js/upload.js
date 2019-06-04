function Upload($file_form, $chat_dialog_template, callback) {
    var server_file_name = '';

    $file_form.unbind().change(function () {
        var $this = this;
        $.ajax({
            type: 'get',
            url: '/upload_get_token',
            data: {},
            dataType: 'json',
            success: function (e) {
                if(e.code == 200) {
                    server_file_name = e.data.token; //每次获取token作为后台文件名
                    addFileAndSend($this);
                } else {
                    alert(e.message);
                }
            },
            error: function () {
                alert('error');
            }
        });
    });

    const LENGTH = 2 * 1024 * 1024;  //每次 2M
    var start;
    var blob;
    var blob_num;
    //对外方法，传入文件对象
    function addFileAndSend(obj) {
        $('.upload_image_progress .progress').css('width', '0%');
        var file = obj.files[0];
        start    = 0;
        blob_num = 1;
        blob = cutFile(file);
        sendFile(blob, file);
        blob_num += 1;
    }

    //切割文件
    function cutFile(file) {
        var file_blob = file.slice(start, start + LENGTH);
        start = start + LENGTH;
        return file_blob;
    }

    //发送文件
    function sendFile(blob, file) {
        var total_blob_num = Math.ceil(file.size / LENGTH);
        var form_data = new FormData();
        form_data.append('file', blob);
        form_data.append('blob_num', blob_num);
        form_data.append('total_blob_num', total_blob_num);
        form_data.append('file_name', file.name);
        form_data.append('server_file_name', server_file_name);
        form_data.append('_token', $('[name="csrf_token"]').val());

        $.ajax({
            type: 'POST',
            url: '/upload_upload',
            data: form_data,
            contentType: false,
            processData: false,
            mimeType: 'multipart/form-data',
            dataType: 'json',
            success: function (rs) {
                if(rs.code == 200 || rs.code == 203) {
                    //显示进度条
                    $('.upload_image_progress').css({
                        'left' : $chat_dialog_template.position().left - 96,
                        'top' : $chat_dialog_template.position().top + 128,
                        'z-index':'1000'
                    }).removeClass('hide');

                    var progress;
                    if (total_blob_num == 1 || start >= file.size) {
                        progress = '100%';
                    } else {
                        progress = Math.min(100, (blob_num / total_blob_num) * 100) + '%';
                    }
                    if(rs.code == 200) {
                        $('.upload_image_progress .progress').css('width', '100%');
                        callback(rs.data.file_name);  //回调函数
                        return true;
                    }
                    setTimeout(function () {
                        $('.upload_image_progress .progress').css('width', progress);
                        if (start < file.size) {
                            blob = cutFile(file);
                            sendFile(blob, file);
                            blob_num += 1;
                        }
                    }, 500);
                } else {
                    $('[name="upload_image_file"]').val('');
                    $('.upload_image_progress').addClass('hide').find('.progress').css('width', '0%');
                    Dialog.error(rs.message, false, true);
                }

            },
            error: function () {
                alert('error');
            }
        });
    }
}