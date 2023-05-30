<head>
    <meta charset="UTF-8">
    <title>face 首页</title>
    <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
</head>
<body>
<label>你的头像</label>
<input type="hidden" name="csrf_token" value="{{ csrf_token() }}">

<input type="file" name="file" id="uploadImageOri" onchange="postData(1)">
<img src="" alt="" id="imgOri" style="width:300px;display:none;" />
<p>================================================================================</p>
<br/>

<label>要替换的</label>
<input type="file" name="file" id="uploadImageDest" onchange="postData(2)">
<img src="" alt="" id="imgDest" style="width:300px;display:none;" />
<p>================================================================================</p>
<br/>

<input type="button" value="生成" onclick="ok()"/>
<img src="" alt="" id="imgOk" style="width:300px;display:none;" />
</body>

<script type="text/javascript">
    function postData(type) {
        var formData = new FormData();
        if (type == 1) {
            formData.append("file", $("#uploadImageOri")[0].files[0]);
            formData.append('_token', $('[name="csrf_token"]').val());
        } else if (type == 2) {
            formData.append("file", $("#uploadImageDest")[0].files[0]);
            formData.append('_token', $('[name="csrf_token"]').val());
        }

        $.ajax({
            url: "{{route('face_upload_file')}}",
            type: "post",
            data: formData,
            processData: false, // 告诉jQuery不要去处理发送的数据
            contentType: false, // 告诉jQuery不要去设置Content-Type请求头
            dataType: 'text',
            success: function (data) {
                console.log(data);
                var params = JSON.parse(data)
                if (type == 1) {
                    $("#imgOri").attr("src", params.data.url).show();
                } else if (type == 2) {
                    $("#imgDest").attr("src", params.data.url).show();
                }
            },
            error: function (data) {

            }
        });
    }

    function ok() {
        var formData = new FormData();

        formData.append("file1", $("#imgOri").attr('src').split('/')[$("#imgOri").attr('src').split('/').length - 1]);
        formData.append("file2", $("#imgDest").attr('src').split('/')[$("#imgDest").attr('src').split('/').length - 1]);
        formData.append('_token', $('[name="csrf_token"]').val());

        $.ajax({
            url: "{{route('face_exec')}}",
            type: "post",
            data: formData,
            processData: false, // 告诉jQuery不要去处理发送的数据
            contentType: false, // 告诉jQuery不要去设置Content-Type请求头
            dataType: 'text',
            success: function (data) {
                console.log(data);
                var params = JSON.parse(data)
                $("#imgOk").attr("src", params.data.url).show();
            },
            error: function (data) {

            }
        });
    }
</script>
