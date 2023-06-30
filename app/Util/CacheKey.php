<?php

namespace App\Util;

/*所有缓存key在这管理*/
class CacheKey
{
    const USER_SINGLE_LOGIN_KEY = 'USER_SINGLE_LOGIN_TOKEN_%s';  //用户单点登录

    const USER_IDS_KEY = 'USER_IDS';  //登录用户的id

    //======== 麻将群组功能相关key ========//
    const DEVICE_UNIQUE_ID_KEY = 'DEVICE_UNIQUE_ID_%s';  // device_unique_id 对应用户信息

    const GROUP_HOME_OWNER_USER_ID_KEY = 'GROUP_HOME_OWNER_USER_ID_%s';  //房主的user_id

    const FD_KEY = 'FD_%s';                              //fd 对应用户信息

    const GROUP_USER_IDS_KEY = 'A_GROUP_USER_IDS_%s';    //组内的用户列表  %s是组（牌桌）的号码

    const PREFIX = 'A_MJ_PREFIX_%s';                     //key的前缀

    const GROUP_IS_START_KEY = 'GROUP_IS_START_%s';      //牌桌是否已经开始游戏

    const USER_CAN_OPERATE_KEY = 'USER_CAN_OPERATE_%s';  //是否可以操作（碰，吃，胡，杠 等）

    const USER_OPERATE_KEY = 'USER_OPERATE_%s';          //用户/机器人 操作（碰，吃，胡，杠 等）   对打出的牌抢占右先权（胡>杠>碰>吃）
}
