<?php
namespace App\Tiktok;

use App\Globals\Ens;
use App\Globals\Http;

use Illuminate\Support\Facades\Storage;
use GuzzleHttp;

class Shop{
	private $domain 			= '';
	private $appid 				= '';
	private $appkey 			= '';
	private $accessTokenUrl 	= '/api/token/getAccessToken';
	private $refressTokenUrl 	= '/api/token/refreshToken';
	private $authUrl 			= 'https://auth.tiktok-shops.com/oauth/authorize';
	public static $resion 		= [
		'GB'	=> '英国',
		'MY'	=> '马来',
		'PH'	=> '菲律宾',
		'SG'	=> '新加坡',
		'TH'	=> '泰国',
		'VN'	=> '越南'
	];

	public function __construct(){
		$this->domain 		= rtrim(env('TIKTOKDOMAIN'), '/');
		$this->appid 		= env('TIKTOKAPPID');
		$this->appkey 		= env('TIKTOKAPPKEY');
		return $this;
	}

	//计算tiktok签名
	public function sign($url, $arr = []){
		$path 			= $url;
		if(empty($arr)){
			$params 	= parse_url($url);
			if(isset($params['query'])){
				$path 	= $params['path'];
				$query 	= parse_url($params['query']);
				$arr 	= [];
				parse_str($params['query'], $arr);
			}else{
				return null;
			}
		}else{
			if(strpos($url, 'http') === 0){
				$params 	= parse_url($url);
				$path 		= $params['path'] ?? $path;
			}
		}
		$arr['timestamp'] 	= time();
		$at 				= '';
		if(isset($arr['access_token'])){
			$at 			= $arr['access_token'];
			unset($arr['access_token']);
		}
		if(isset($arr['sign'])){
			unset($arr['sign']);
		}
		ksort($arr);
		$str 	= '';
		foreach($arr as $k => $v){
			$str 	.= $k . $v;
		}
		$input	= $this->appkey . $path . $str . $this->appkey;
		$hash 	= hash_hmac('sha256', $input, $this->appkey);
		$arr['sign']	= $hash;
		if($at){
			$arr['access_token'] 	= $at;
		}
		return $arr;
	}

	//获取授权码
	public function accesstoken($code){
		$url 	= sprintf('%s%s', 'https://auth.tiktok-shops.com', $this->accessTokenUrl);
		return Http::post($url, ['app_key' => $this->appid, 'app_secret' => $this->appkey, 'auth_code' => $code, 'grant_type' => 'authorized_code']);
	}

	//刷新授权码
	public function refreshaccesstoken($token){
		$url 	= sprintf('%s%s', 'https://auth.tiktok-shops.com', $this->refressTokenUrl);
		return Http::post($url, ['app_key' => $this->appid, 'app_secret' => $this->appkey, 'refresh_token' => $token, 'grant_type' => 'refresh_token']);
	}

	//获取授权跳转链接
	public function authurl($adminid){
		return $this->authUrl . '?app_key=' . $this->appid . '&state=' . base64_encode(Ens::encrypt('{"id": "'.$adminid.'","time":'.time().'}'));
	}

	public function defaultParams($uri, $accesstoken, $arr = []){
		if(!is_array($arr)){
			$arr 				= [];
		}
		$arr['app_key'] 		= $this->appid;
		$arr['access_token']	= $accesstoken;
		return $this->sign($uri, $arr);
	}


	//获取活跃商店列表
	public function ActiveShops($access_token, $adminid){
		$uri 		= '/api/seller/global/active_shops';

		$arr 		= $this->defaultParams($uri, $access_token);
		$url 		= $this->domain . $uri . '?' . http_build_query($arr);
		$res 		= json_decode(Http::get($url), true);
		if(!isset($res['code'])){
			return '未知错误,可能超时或其他原因!';
		}
		if($res['code'] != 0){
			return isset($res['message']) ? $res['message'] : 'Tiktok返回错误!';
		}
		if(!isset($res['data']['active_shops'])){
			return '未知错误,可能超时或其他原因-缺少data';
		}
		$list 		= $res['data']['active_shops'];
		return $list;
	}

	//获取产品列表
	//从tk端获取,本地不缓存
	public function ProductLists($access_token, $shopId, $page = 1, $limit = 100, $search_status = null){
		$uri 		= '/api/products/search';
		$arr 		= $this->defaultParams($uri, $access_token, ['shop_id' => $shopId]);

		$data 		= [
			'page_number'		=> (int)$page,
			'page_size'			=> (int)$limit,
		];
		if($search_status){
			$data['search_status']		= $search_status;
		}

		$res 		= Http::post($this->domain . $uri . '?' . http_build_query($arr), $data);
		$res 		= json_decode($res, true);
		if(!isset($res['code'])){
			return '未知错误,可能超时或其他原因!';
		}
		if($res['code'] != 0){
			return isset($res['message']) ? $res['message'] : 'Tiktok返回错误!';
		}
		if(!isset($res['data']['products'])){
			return '未知错误,可能超时或其他原因-缺少data';
		}
		return $res['data'];
	}

	//同步商品详情
	public function ProductInfo(array $ids, string $accesstoken, int $shopid){
		if(empty($ids)){
			return false;
		}
		$idslen 	= count($ids);
		$limit 		= 20;
		$pages 		= ceil($idslen / $limit);

		for($i = 1; $i <= $pages; $i++){
			$params 	= [
				'access_token'	=> $accesstoken,
				'shop_id'		=> $shopid,
				'callback'		=> route('api.tiktok.proinfo'),
				'ids'			=> implode(',', array_slice($ids, ($i-1)*$limit, $limit)),
			];
			try {
	            $http = new GuzzleHttp\Client;
	            $response = $http->post(env('TIKTOK_GO_URL') . 'proinfo', [
	                'form_params' => $params,
	            ]);
	            $res    = (string)$response->getBody();
	            $res 	= json_decode($res, true);
	            if(!isset($res['code']) || $res['code'] != 200){
	            	return $res['msg'] ?? '未知错误!';
	            }
	        } catch (\Exception $e) {
	            return $e->getMessage();
	        }
		}
		return true;

		
		// if(empty($ids)){
		// 	return false;
		// }
		// $params 	= [
		// 	'-at'		=> $accesstoken,
		// 	'-appkey'	=> $this->appid,
		// 	'-secret'	=> $this->appkey,
		// 	'-si'		=> $shopid,
		// 	'-callback'=> route('api.tiktok.proinfo'),
		// 	'-ids'		=> implode(',', $ids),
		// ];

		// $str 		= Storage::path('tkpros.exe');
		// foreach($params as $k => $item){
		// 	$str 	.= ' ' . $k . ' ' . $item;
		// }
		// $out 		= '';
		// exec($str, $out);
		// return $out;
	}


	//获取店铺所有订单列表
	public function OrderLists($access_token, $shopId){
		set_time_limit(0);
		$uri 		= '/api/orders/search';
		$arr 		= $this->defaultParams($uri, $access_token, ['shop_id' => $shopId]);
		$orders 	= [];

		$limit 		= 50;
		$data 		= [
			'page_size'			=> $limit,
		];

		//首次执行
		$url 		= $this->domain . $uri . '?' . http_build_query($arr);
		$res 		= $this->getodlist($url, $data);
		if(!is_array($res)){
			return $res;
		}

		try {
			$pages 				= ceil((int)$res['total'] / $limit);
			$data['cursor']		= $res['next_cursor'];
			$hasMore 			= $res['more'];
			$list 				= $res['order_list'] ?? [];
			if(is_array($list) && !empty($list)){
				foreach($list as $item){
					$orders[] 		= ['order_id' => $item['order_id'], 'order_status' => $item['order_status']];
				}
			}

			if($hasMore == true){
				while(true) {
					$res 		= $this->getodlist($url, $data);
					if(!is_array($res)){
						return $res;
					}
					$data['cursor']		= $res['next_cursor'];
					$list 				= $res['order_list'] ?? [];
					if(is_array($list) && !empty($list)){
						foreach($list as $item){
							$orders[] 		= ['order_id' => $item['order_id'], 'order_status' => $item['order_status']];
						}
					}
					if($res['more'] !== true){
						break;
					}
					if(--$pages < 1){
						break;
					}
				}
			}
			return $orders;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	//真正执行获取订单的方法
	private function getodlist($url, $data){
		$res 		= Http::post($url, $data);
		$res 		= json_decode($res, true);
		if(!isset($res['code'])){
			return '未知错误,可能超时或其他原因!';
		}
		if($res['code'] != 0){
			return isset($res['message']) ? $res['message'] : 'Tiktok返回错误!';
		}
		if(!isset($res['data'])){
			return '未知错误,可能超时或其他原因-缺少data';
		}
		return $res['data'];
	}

	//同步订单详情
	public function SyncOrderInfo(array $ids, string $accesstoken, string $shopid){
		if(empty($ids)){
			return false;
		}


		// $uri 		= '/api/orders/detail/query';
		// $arr 		= $this->defaultParams($uri, $accesstoken, ['shop_id' => $shopid, 'orderSeller' => '7494083165931603338']);
		// $url 		= $this->domain . $uri . '?' . http_build_query($arr);
		// $res 		= Http::post($url, ['order_id_list' => $ids]);
		// dd($url, $res);


		$idslen 	= count($ids);
		$limit 		= 20;
		$pages 		= ceil($idslen / $limit);

		for($i = 1; $i <= $pages; $i++){
			$params 	= [
				'access_token'	=> $accesstoken,
				'shop_id'		=> $shopid,
				'callback'		=> route('api.tiktok.orderinfo'),
				'ids'			=> implode(',', array_slice($ids, ($i-1)*$limit, $limit)),
			];
			try {
				$http = new GuzzleHttp\Client;
				$response = $http->post(env('TIKTOK_GO_URL') . 'orderinfo', [
					'form_params' => $params,
				]);
				$res    = (string)$response->getBody();
				$res 	= json_decode($res, true);
				if(!isset($res['code']) || $res['code'] != 200){
					return $res['msg'] ?? '未知错误!';
				}
			}catch (\Exception $e) {
				return $e->getMessage();
			}
		}
		return true;
	}
}
