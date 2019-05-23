<?php

namespace App\Models;

class WebSocket
{
    //会话类型
    const TYPE_USER_LIST   = 1;
    const TYPE_MSG         = 2;
    const TYPE_USER_LOGIN  = 3;
    const TYPE_USER_LOGOUT = 4;
    const TYPE_OFFLINE_MSG = 5;

    //分割符
    const SPLIT_WORD = '{-$☋$-}';
}

