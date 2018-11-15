<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>登录</title>
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

        #login-form {
            margin-top: 20px;
        }

        #login-form .input-group {
            margin-bottom: 15px;
        }

        #register-form {
            margin-top: 20px;
        }

        #register-form .input-group {
            margin-bottom: 15px;
        }

        .hide {
            display: none;
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

                    <form action="{{route('user_login')}}" method="post" id="login-form">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="fa fa-user" aria-hidden="true"></span>
                            </div>
                            <input type="text" class="form-control" placeholder="用户名"
                                   name="name" autocomplete="off" value=""/>
                        </div>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="fa fa-key" aria-hidden="true"></span>
                            </div>
                            <input type="password" class="form-control" placeholder="密码"
                                   name="password" autocomplete="off" value=""/>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg btn-block">登 录</button>
                            <a id="toRegister" href="#">没有账号?去注册</a>
                            <a style="float: right;" href="{{ route('tourist_login') }}">游客登录</a>
                        </div>
                    </form>

                    <form action="{{route('user_register')}}" method="post" id="register-form" class="hide">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="fa fa-user" aria-hidden="true"></span>
                            </div>
                            <input type="text" class="form-control" placeholder="用户名"
                                   name="name" autocomplete="off" value=""/>
                        </div>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="fa fa-key" aria-hidden="true"></span>
                            </div>
                            <input type="password" class="form-control" placeholder="密码"
                                   name="password" autocomplete="off" value=""/>
                        </div>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="fa fa-key" aria-hidden="true"></span>
                            </div>
                            <input type="password" class="form-control" placeholder="确认密码"
                                   name="re_password" autocomplete="off" value=""/>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg btn-block">注 册</button>
                            <a id="toLogin" href="#">已有账号?去登录</a>
                            <a style="float: right;" href="{{ route('tourist_login') }}">游客登录</a>
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

        //切换到注册
        $('#toRegister').click(function () {
            $('#register-form').removeClass('hide');
            $('#login-form').addClass('hide');
            return false;
        });
        //切换到登录
        $('#toLogin').click(function () {
            $('#register-form').addClass('hide');
            $('#login-form').removeClass('hide');
            return false;
        });

        //切换登录/注册
        tabLoginOrRegister();
    });

    function tabLoginOrRegister() {
        var type = GetUrlQueryString('type');
        if (type == 'register') {
            $('#toRegister').click();
        } else {
            $('#toLogin').click();
        }
    }
</script>
</html>
