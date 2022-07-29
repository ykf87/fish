<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TiktokProduct;

class ProductController extends Controller{
	//首页
	public function options(Request $request){
		$q 			= trim($request->input('search'));
		$c 			= (int)$request->input('category');
		$s 			= (int)$request->input('sort');
		$page		= (int)$request->input('page');
		$limit		= (int)$request->input('limit');

		$tp 		= new TiktokProduct;

		return [
			'code' => 200,
			'msg' => 'Success',
			'data' => $tp->frontList($page, $limit, $q, $c, $s)
		];
	}

	//详情
	public function detail(Request $request){
		$id 		= $request->input('id');
		
	}
}
