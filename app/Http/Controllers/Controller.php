<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	public function success($data, $msg = ''){
		if(!$msg){
			$msg 	= 'Success';
		}
		return response()->json([
			'code'=> 200,
			'msg'=> __($msg),
			'data'=> $data,
		]);
	}
}
