<?php
namespace App\Tiktok;

use App\Globals\Ens;
use App\Globals\Http;

class Shop{
	private $domain 			= '';
	private $appid 				= '';
	private $appkey 			= '';
	private $accessTokenUrl 	= '/api/token/getAccessToken';
	private $refressTokenUrl 	= '/api/token/refreshToken';
	private $authUrl 			= 'https://auth.tiktok-shops.com/oauth/authorize';

	public function __construct(){
		$this->domain 		= rtrim(env('TIKTOKDOMAIN'), '/');
		$this->appid 		= env('TIKTOKAPPID');
		$this->appkey 		= env('TIKTOKAPPKEY');
		return $this;
	}

	//计算tiktok签名
	public function sign(){

	}

	//获取授权码
	public function accesstoken($code){
		$url 	= sprintf('%s%s', 'https://auth.tiktok-shops.com', $this->accessTokenUrl);
		$res 	= Http::post($url, ['app_key' => $this->appid, 'app_secret' => $this->appkey, 'auth_code' => $code, 'grant_type' => 'authorized_code']);
	}

	//刷新授权码
	public function refreshaccesstoken(){

	}

	//获取授权跳转链接
	public function authurl($adminid){
		return $this->authUrl . '?app_key=' . $this->appid . '&state=' . base64_encode(Ens::encrypt('{"id": "'.$adminid.'","time":'.time().'}'));
	}
}
