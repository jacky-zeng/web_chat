<?php

namespace App\Util;

/*所有缓存key在这管理*/
class CacheKey
{
    const USER_SINGLE_LOGIN_KEY = 'USER_SINGLE_LOGIN_TOKEN_%s';  //用户单点登录

    const USER_IDS_KEY = 'USER_IDS';  //登录用户的id

    //======== 麻将群组功能相关key ========//
    const DEVICE_UNIQUE_ID_KEY = 'DEVICE_UNIQUE_ID_%s';  // device_unique_id 对应用户信息

    const FD_KEY = 'FD_%s';                              // fd 对应用户信息

    const GROUP_USER_IDS_KEY = 'GROUP_USER_IDS_%s';  //组内的用户列表  %s是组（牌桌）的号码
}
