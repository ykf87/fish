<?php
namespace App\Platforms\Tiktok;
class Shop{
	private $endpoint = 'https://open-api.tiktokglobalshop.com';
	private $appid = null;
	private $appkey = null;

	public function __controller($appid, $appkey){
		$obj 			= new self;
		$obj->appid 	= $appid;
		$obj->appkey 	= $appkey;
	}
}