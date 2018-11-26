<?php

namespace App\Util;

/*加解密*/
class EnDecryption
{
    const KEY = 'd8k9r5fs98erx9';

    public static function encrypt($user_id)
    {
        $round_str = '';
        $round_arr = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $round_str .= $round_arr[$user_id % 9] . $round_arr[$user_id % 7] . $round_arr[$user_id % 5] . $round_arr[$user_id % 3] . $round_arr[$user_id % 2];
        //$round_str用于混淆（不然的话，生成的加密字符串太相似了），解密时会丢弃掉前5个字符
        $data = $round_str.strval($user_id);
        $key  = self::KEY;
        $key  = md5($key);
        $x    = 0;
        $len  = strlen($data);
        $l    = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }

        return base64_encode($str);
    }

    public static function decrypt($data)
    {
        $data = strval($data);
        $key  = self::KEY;
        $key  = md5($key);
        $x    = 0;
        $data = base64_decode($data);
        $len  = strlen($data);
        $l    = strlen($key);

        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }

        return substr($str, 5);
    }
}