<?php

namespace App\Globals;

class Ens{
    public static $versionKeys = [
        100   => [
            'key'   => 'nytSDzCa$G6*hy-i',
            'iv'    => '0zfeYUf5HP2qaZ8d',
        ],
    ];
    // private static $method      = 'AES-128-ECB';
    private static $method      = 'AES-128-ECB';
    private static $padding     = OPENSSL_RAW_DATA;
    public static $timeout      = 180;// 5分钟有效

    /**
     * aes 加密
     */
    public static function encrypt($str, $versioncode = 100){
        // if(!isset(self::$versionKeys[$versioncode])){
        //     return false;
        // }
        // if(!is_string($word)){
        //     $word   = json_encode($word);
        // }
        // $key        = self::$versionKeys[$versioncode]['key'];
        // $iv         = self::$versionKeys[$versioncode]['iv'];

        // $res        = openssl_encrypt($word, self::$method, $key, self::$padding);
        // if(!$tp){
        //     $res        = bin2hex($res);
        // }else{
        //     $res        = base64_encode($res);
        // }
        // return $res;
        $key        = self::$versionKeys[$versioncode]['key'];
        $iv         = self::$versionKeys[$versioncode]['iv'];
        if(self::$method == 'AES-128-ECB'){
            return base64_encode(openssl_encrypt($str,self::$method,$key, self::$padding));
        }
        return base64_encode(openssl_encrypt($str,self::$method,$key, self::$padding,$iv));
    }

    /**
     * aes 解密
     */
    public static function decrypt($token, $versioncode = 100){
        $key        = self::$versionKeys[$versioncode]['key'];
        $iv         = self::$versionKeys[$versioncode]['iv'];
        $token      = str_replace(['_', '-'], ['/', '+'], $token);
        if(self::$method == 'AES-128-ECB'){
            return openssl_decrypt(base64_decode($token),self::$method,$key, self::$padding);
        }
        return openssl_decrypt(base64_decode($token),self::$method,$key, self::$padding,$iv);
        // if(!isset(self::$versionKeys[$versioncode])){
        //     return false;
        // }

        // if(!$tp){
        //     $token      = hex2bin($token);
        // }else{
        //     $token      = base64_decode($token);
        // }

        // $key        = self::$versionKeys[$versioncode]['key'];
        // $iv         = self::$versionKeys[$versioncode]['iv'];
        // return openssl_decrypt($token, self::$method, $key, self::$padding, $iv);
    }
}
