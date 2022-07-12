<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Globals\Ens;

class CallbackController extends Controller{
	public function index(Request $request){
		$state 		= $request->get('state');
		$code 		= $request->get('code');
		if(!$code || !$state){
			return abort(404);
		}
		$str 		= Ens::decrypt(base64_decode($state));
		$res 		= json_decode($str, true);
		if(!isset($res['id']) || !isset($res['time'])){
			return abort(404);
		}
		$arr 		= [];
		if((time()-$res['time']) > 600){
			$arr['err'] 	= '授权超时!';
		}else{
			$arr['code'] 	= $code;
			$arr['id']		= $res['id'];
		}
		header('location:' . route('admin.tiktok-shops.addnew', $arr));
	}
}
