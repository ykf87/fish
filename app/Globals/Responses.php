<?php

namespace App\Globals;

class Responses{
    /**
     * 成功返回
     */
    public static function success($data = null, $msg = '', $code = 200, $httpcode = 200){
        return self::resp($msg, $data, $code, $httpcode);
    }

    /**
     * 错误返回
     */
    public static function error($msg, $data = null, $code = 500, $httpcode = 200){
        return self::resp($msg, $data, $code, $httpcode);
    }

    /**
     * 统一的返回
     */
    public static function resp($msg = '', $data = null, $code = 200, $httpcode = 200){
        if(is_array($data)){
            if(isset($data[0])){
                $dd             = $data;
                $data           = [];
                $data['list']   = $dd;
            }
        }
        if(!$data){
            $data   = [];
        }
        $arr        = [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data,
        ];
        return response()->json($arr, $httpcode);
    }
}
