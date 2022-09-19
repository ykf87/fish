<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TiktokOrder;
use App\Models\TiktokProduct;
use App\Models\TiktokOrderProduct;

class IndexController extends Controller{
	public function index(Request $request){
		$days 		= (int)$request->input('days');
		// $state 		= $request->input('state');
		// $account_id	= $request->input('account_id');
		$page		= (int)$request->input('order_page');
		$limit 		= (int)$request->input('order_limit');
		if($page < 1){
			$page 	= 1;
		}
		if($limit < 1){
			$limit 	= 20;
		}
		if($days < 1){
			$days 			= 3650;
		}
		$days 				*= -1;
		$addTimeMust 		= strtotime($days . ' days');

		$arr 				= [];
		$totalNum 			= TiktokOrder::where('addtime', '>', $addTimeMust)->count();
		if($page <= 1){
			$allcomm 			= TiktokOrder::where('addtime', '>', $addTimeMust)->sum('commission');
			$havecomm 			= TiktokOrder::where('addtime', '>', $addTimeMust)->whereNotIn('status', [100,140])->sum('commission');
			if($allcomm < 0.01){
				$allcomm 		= $totalNum * 0.01;
			}
			if($havecomm < 0.01){
				$havecomm 		= $allcomm;
			}
			$arr['fund_data'] 	= [
				'total_sales'			=> TiktokOrder::where('addtime', '>', $addTimeMust)->sum('total_amount'),
				'effective_sales'		=> TiktokOrder::where('addtime', '>', $addTimeMust)->whereNotIn('status', [100,140])->sum('total_amount'),
				'full_commission'		=> $allcomm,
				'effective_commission'	=> $havecomm,
				'all_singular'			=> $totalNum,
				'effective_singular'	=> TiktokOrder::where('addtime', '>', $addTimeMust)->whereNotIn('status', [100,140])->count(),
			]; 
		}
		$order_list = TiktokOrder::list($page, $limit)->where('addtime', '>', $addTimeMust)->get();
        $order_list->flatMap(function($val) {
            $val->left_icon = config('currency.' . $val->currency) ?? '$';
        });
		$arr['order']		= [
			'order_page'		=> $page,
			'order_limit'		=> $limit,
			'order_total_limit'	=> $totalNum,
			'orderList'			=> $order_list,
		];
		return $this->success($arr);
	}

	//排行版
	public function ranking(Request $request){
		$type 		= (int)$request->input('rank_type');
		$days 		= (int)$request->input('days');
		$page		= (int)$request->input('page');
		$limit 		= (int)$request->input('limit');

		if($page < 1){
			$page 	= 1;
		}
		if($limit < 1){
			$limit 	= 20;
		}

		$list 		= [];
		$total 		= 0;
		if($days > 0){
			$days 				*= -1;
			$addTimeMust 		= strtotime($days . ' days');
			$list 				= TiktokOrderProduct::list($addTimeMust)->orderByDesc('cumulative_sales')->groupBy('op.product_id')->offset(($page-1)*$limit)->limit($limit)->get();
			$total 				= TiktokOrderProduct::list($addTimeMust)->groupBy('op.product_id')->get()->count();
		}else{
			$list 				= TiktokProduct::list()->orderByDesc('gmv')->offset(($page-1)*$limit)->limit($limit)->get();
			$total 				= TiktokProduct::list()->count();
		}

        $list->flatMap(function($val) {
            $val->left_icon = config('currency.' . $val->currency) ?? '$';
        });
		
		return $this->success([
			'merchandise_ranking' => ['total_limit' => $total, 'rank_lists' => $list, 'page' => $page, 'limit' => $limit],
		]);
	}
}
