<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TiktokSample;

class SampleController extends Controller{
	//样品列表
	public function index(Request $request){
		$user 		= $request->get('_user');
		$page 		= (int)$request->input('page');
		$limit 		= (int)$request->input('limit');
		$state 		= (int)$request->input('state');

		if($page < 1){
			$page 	= 1;
		}
		if($limit < 1){
			$limit 	= 20;
		}

		$all 		= TiktokSample::select('status')->where('account_id', $user->id)->get();
		$wait_examine_nums 		= 0;
		$wait_delivery_nums 	= 0;
		$shipping_nums 			= 0;
		$arrived_nums 			= 0;
		$complete_nums 			= 0;
		$totals 				= count($all);
		foreach($all as $item){
			switch($item->status){
				case 0:
					$wait_examine_nums++;
					break;
				case 1:
					$wait_delivery_nums++;
					break;
				case 2:
					$shipping_nums++;
					break;
				case 3:
					$arrived_nums++;
					break;
				case -1:
				case -2:
					$complete_nums++;
					break;
			}
		}
		$arr 		= [
			'all_nums'				=> $totals,
			'wait_examine_nums'		=> $wait_examine_nums,
			'wait_delivery_nums'	=> $wait_delivery_nums,
			'shipping_nums'			=> $shipping_nums,
			'arrived_nums'			=> $arrived_nums,
			'complete_nums'			=> $complete_nums,
		];

		$obj 		= TiktokSample::select('s.*', 'p.commission_price as commission', 'p.minprice as unit_price', 'p.currency', 'p.commission as commission_ratio', 's.shippment as express_company', 's.shipnum as express_no')->
						from('tiktok_samples as s')->where('s.account_id', $user->id)->rightJoin('tiktok_products as p', 's.pid', '=', 'p.id');
		switch($state){
			case 2:
				$obj	= $obj->where('s.status', 0);
				break;
			case 3:
				$obj 	= $obj->where('s.status', 1);
				break;
			case 4:
				$obj 	= $obj->where('s.status', 2);
				break;
			case 5:
				$obj 	= $obj->where('s.status', 3);
				break;
			case 6:
				$obj 	= $obj->whereIn('s.status', [-1,-2]);
				break;
		}
		$oss 			= clone $obj;
		// $obj 			= $obj->offset(($page-1)*$limit)->limit($limit)->get();
        $lists = $obj->offset(($page-1)*$limit)->limit($limit)->orderByDesc('id')->get();
        $lists->flatMap(function($val) {
            $val->left_icon = config('currency.' . $val->currency) ?? '$';
        });

		$arr['lists']			= $lists;
		$arr['total_limit']		= $oss->count();
		return $this->success($arr, 'Succcess');
	}

	//取消领样申请
	public function unapply(Request $request){
		$user 			= $request->get('_user');
		$id 			= (int)$request->input('id');

		$row 			= TiktokSample::find($id);
		if(!$row || $row->account_id != $user->id){
			return $this->error('Sample request does not exist');
		}

		if($row->status != 0){
			return $this->error('Cannot be cancelled');
		}
		$row->status 	= -2;
		if($row->save()){
			return $this->success(null, 'Cancellation success');
		}
		return $this->error('Cancellation Failure');
	}
}
