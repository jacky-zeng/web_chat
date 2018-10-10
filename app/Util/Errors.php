<?php

namespace App\Util;

trait Errors
{
    protected $_error = [];

    /**
     * 将错误信息加入数组
     * @param $msg
     * @return bool
     */
    public function error($msg)
    {
        $arguments = array_slice(func_get_args(), 0);
        $msg       = call_user_func_array('sprintf', $arguments);
        array_push($this->_error, $msg);

        return false;
    }

    /**
     * 所有的错误信息
     * @return array
     */
    public function msg()
    {
        return $this->_error;
    }

    /**
     * 第一条错误信息
     * @param string $msg
     * @return string
     */
    public function firstMsg($msg = '操作失败')
    {
        return current($this->_error)?:$msg;
    }
}
