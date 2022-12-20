<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Yaoqing;
use App\Models\User;
use App\Models\Banner;

class IndexController extends Controller{
	public function index(Request $request){
		$invi 			= $request->input('invo');
		if($invi){
			$inviUser 	= User::where('invitation_code', $invi)->first();
			if($inviUser){
				$yaoqing 	= new Yaoqing;
				$yaoqing->key 	= $inviUser->id;
				$yaoqing->ip 	= $request->ip();
				$yaoqing->country 	= $request->get('_resion')['iso'] ?? null;
				$yaoqing->addtime 	= time();
				$yaoqing->save();
			}
		}
		header('Location:' . str_replace('://api', '://www', url('?invi=' . $invi)),TRUE,301);
	}

	public function banner(Request $request){
		return $this->success(Banner::orderByDesc('orderby')->limit(6)->get());
	}
}
