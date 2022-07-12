<?php
namespace App\Tiktok;

use App\Globals\Ens;

class Shop{
	private $domain 			= '';
	private $appid 				= '';
	private $appkey 			= '';
	private $accessTokenUrl 	= '/api/token/getAccessToken';
	private $refressTokenUrl 	= '/api/token/refreshToken';
	private $authUrl 			= 'https://auth.tiktok-shops.com/oauth/authorize';

	public function __construct(){
		$this->domain 		= env('TIKTOKDOMAIN');
		$this->appid 		= env('TIKTOKAPPID');
		$this->appkey 		= env('TIKTOKAPPKEY');
		return $this;
	}

	//计算tiktok签名
	private function sign(){

	}

	//获取授权码
	private function accesstoken(){

	}

	//刷新授权码
	private function refreshaccesstoken(){

	}

	//获取授权跳转链接
	public function authurl($adminid){
		return $this->authUrl . '?app_key=' . $this->appid . '&state=' . base64_encode(Ens::encrypt('{"id": "'.$adminid.'","time":'.time().'}'));
	}
}
