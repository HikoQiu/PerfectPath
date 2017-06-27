<?php
/**
 * Created by PhpStorm.
 * User: hikoqiu
 * Date: 2017/6/28
 */

namespace src\utl;


class ResponseUtl
{
    const CODE_SUCC = 0;
    const CODE_FAIL = 1001;

    public static function msg($code, $msg = '', array $data)
    {
        return json_encode([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);

    }

    public static function succ(array $data, $msg = 'succ')
    {
        return self::msg(self::CODE_SUCC, $msg, $data);
    }

    public static function fail($code, $msg = 'fail', array $data = [])
    {
        return self::msg($code, $msg, $data);
    }
}