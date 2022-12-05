<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Yaoqing;
use App\Models\User;

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
			}
		}
		header("Location:https://www.domefish.com",TRUE,301);
	}
}
