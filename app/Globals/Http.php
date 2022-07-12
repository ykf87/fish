<?php
namespace App\Globals;

use GuzzleHttp\Client;
class Http{
	private static $client = null;
	private static function getClient(){
		if(!self::$client){
			self::$client = new Client;
		}
		return self::$client;
	}

	public static function get($url, $header = []){
		$res = self::getClient()->request('GET', $url);
		return (string)$res->getBody();
	}
	public static function post($url, $data = [], $header = []){
		$res = self::getClient()->request('POST', $url, [
			'form_params' 	=> $data
		]);
		return (string)$res->getBody();
	}
}