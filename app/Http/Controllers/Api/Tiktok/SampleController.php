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

		$obj 		= TiktokSample::where('account_id', $user->id);
		switch($state){
			case 2:
				$obj	= $obj->where('status', 0);
				break;
			case 3:
				$obj 	= $obj->where('status', 1);
				break;
			case 4:
				$obj 	= $obj->where('status', 2);
				break;
			case 5:
			case 6:
				$obj 	= $obj->where('status', 3);
				break;
		}
		$obj 		= $obj->offset(($page-1)*$limit)->limit($limit)->get();
		return $this->success($obj, 'Succcess');
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
