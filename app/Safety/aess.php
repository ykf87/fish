<?php
namespace App\Safety;

class Aess{
	private static $key 		= 'j!=i1@~w=3FC?a9Y';
    private static $method      = 'AES-128-ECB';
    // private static $method      = 'AES-256-CBC';
    private static $iv 			= '';
    private static $padding     = OPENSSL_RAW_DATA;
    public static $timeout      = 180;// 5分钟有效

	public static function decode($token){
		$token 		= urldecode($token);
		// dd($token);
		// return openssl_decrypt($token, 'AES-256-CBC', self::$key, OPENSSL_RAW_DATA, self::$iv);
		$key 		= self::$key;
		$iv 		= self::$iv;
		if(self::$method == 'AES-128-ECB'){
			return openssl_decrypt(base64_decode($token),self::$method,$key, self::$padding);
		}
		return openssl_decrypt(base64_decode($token),self::$method,$key, self::$padding,$iv);
	}
}
