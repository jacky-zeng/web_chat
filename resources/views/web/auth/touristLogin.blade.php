<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>游客登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <!-- Loading Bootstrap -->
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/font-awesome.min.css">
    <link rel="stylesheet" href="/css/common.css">
    <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="/js/common.js"></script>
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">
        body {
            color:rgba(161,165,185,1);
            font-family:PingFangSC-Regular;
            background: url('http://www.zengyanqi.com/wp-content/themes/twentyseventeen/assets/images/header.jpg') no-repeat center/cover;
            height: 100vh;
        }

        a {
            color: #fff;
        }

        .login-screen {
            width: 400px;
            height: 350px;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -200px;
            margin-top: -170px;
        }

        .login-screen .well {
            border-radius: 3px;
            -webkit-box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
            background: rgba(21, 22, 23, 0.8);
        }

        .login-screen .copyright {
            position: fixed;
            bottom: 8px;
            text-align: center;
            margin-left: 135px;
        }

        @media (max-width: 767px) {
            .login-screen {
                padding: 0 20px;
            }
        }

        .profile-img-card {
            width: 100px;
            height: 100px;
            margin: 10px auto;
            display: block;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
        }

        .profile-name-card {
            text-align: center;
        }

        form {
            margin-top: 20px;
        }

        form .input-group {
            margin-bottom: 15px;
        }

        form .dice {
            display: inline-block;
            width: 19px;
            cursor: pointer;
        }
        form .dice img {
            border-radius: 2px;
            width: 34px;
            line-height: 33px;
            background-color: white;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="login-wrapper">
        <div class="login-screen">
            <div class="well">
                <div class="login-form">
                    <img id="profile-img" class="profile-img-card" src="/img/common/login_avatar.png"/>
                    <p id="profile-name" class="profile-name-card" style="color:#fff;"></p>

                    <form action="{{route('tourist_login')}}" method="post">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="fa fa-user" aria-hidden="true"></span>
                            </div>
                            <input type="text" class="form-control" style="width: 90%;" placeholder="游客名称"
                                   name="nick_name" autocomplete="off" value="{{$nick_name}}"/>
                            <div title="换一个" class="dice" btn="getNickName">
                                <img src="/img/common/saizi7.gif"></div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg btn-block">进入系统</button>
                            <a id="toLogin" href="{{ route('user_login') }}">已有账号?去登录</a>
                        </div>
                    </form>

                </div>
            </div>
            <p class="copyright">
                <a href="http://www.zengyanqi.com" target="_blank">Powered By ZengYanQi</a>
            </p>
        </div>
    </div>
</div>
</body>
<script type="text/javascript">
    $(function () {
        //错误提示
        @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
                Dialog.error('{{$error}}', false, true);
            @endforeach
        @endif

        $('[btn="getNickName"]').click(function () {
            var $this = $(this);
            $this.find('img').attr('src', '/img/common/saizi3.jpg').attr('src', '/img/common/saizi7.gif');
            setTimeout(function () {
                $.ajax({
                    type: "get",
                    url: "{{route('get_nick_name')}}",
                    data: {},
                    dataType: "json",
                    success: function (e) {
                        if (e.code == 200) {
                            $('[name="nick_name"]').val(e.data.nick_name);
                        }
                    }
                });
            }, 1500);
        });
    });

</script>
</html>
