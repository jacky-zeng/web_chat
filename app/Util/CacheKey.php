<?php

namespace App\Util;

/*所有缓存key在这管理*/
class CacheKey
{
    const USER_SINGLE_LOGIN_KEY = 'USER_SINGLE_LOGIN_TOKEN_%s';  //用户单点登录

    const USER_IDS_KEY = 'USER_IDS';  //登录用户的id
}