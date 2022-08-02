<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TiktokProduct;
use App\Models\TiktokCategory;

class ProductController extends Controller{
	//选品首页
	public function index(Request $request){
		$cateLists 		= TiktokCategory::where('parent', 0)->pluck('name', 'id');
		$banner 		= [
			[
				'id'	=> 1,
				'des'	=> 'test',
				'image'	=> '',
				'url'	=> '',
			],[
				'id'	=> 1,
				'des'	=> 'test',
				'image'	=> '',
				'url'	=> '',
			]
		];
		return $this->success(['category_lists' => $cateLists, 'banner' => $banner]);
	}

	//产品首页
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
