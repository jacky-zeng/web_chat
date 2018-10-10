<?php

namespace App\Util;

class Code
{
    const SUCCESS                    = 200;     //请求ok
    const OPERATE_FAIL               = 201;     //请求操作失败
    const PARAMS_ERROR               = 204;     //参数错误
    const RESULT_EMPTY               = 205;     //查询到的结果为空
    const AUTH_INVALID               = 401;     //登录失效
    const AUTH_TOKEN_INVALID         = 4010;     //登录失效

    /** 内部错误 */
    const INTERNAL_ERROR             = 5001;    //系统繁忙

    private static $message = [
        self::SUCCESS                => "成功",
        self::OPERATE_FAIL           => "失败",
        self::PARAMS_ERROR           => "参数错误",
        self::INTERNAL_ERROR         => "系统繁忙",
        self::RESULT_EMPTY           => "结果为空",
        self::AUTH_INVALID           => "登录失效",
    ];

    /**
     * 翻译 Code
     * @param integer $code
     * @return string
     */
    public static function getCodeMessage($code)
    {
        if (isset(static::$message[$code])) {
            return static::$message[$code];
        }

        // 不能识别的全部按内部错误处理
        return static::$message[self::INTERNAL_ERROR];
    }
}