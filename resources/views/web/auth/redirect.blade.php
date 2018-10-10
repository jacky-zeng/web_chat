<!DOCTYPE html>
<html>
<head>
    <title>登录失效</title>
    <script type="text/javascript">
        //异步请求登录失效时，跳转到登录页面
        window.top.location.href = '{{ route('user_login') }}'
    </script>
</head>
<body>
</body>
</html>
