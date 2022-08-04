<?php

namespace App\Http\Controllers\Api\Tiktok;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TiktokDarren;

class DarrenController extends Controller{
	//tiktok 达人账号列表
	public function index(Request $request){
		$user 		= $request->get('_user');

		$page 		= (int)$request->input('page');
		$limit 		= (int)$request->input('limit');
		$q 			= trim($request->input('search'));
		if($page < 1)$page = 1;
		if($limit < 1)$limit = 20;

		// $total 		= TiktokDarren::lists($user->id, $q)->count();
		$list 		= TiktokDarren::lists($user->id, $q)->offset(($page-1)*$limit)->limit($limit)->get();
		return $this->success($list, '');
	}

	//添加tiktok账号
	public function add(Request $request){
		$user 		= $request->get('_user');

		$unionid	= $request->input('account');
		$nickname	= $request->input('nickname');
		$backend 	= $request->file('backend');
		$fans 		= (int)$request->input('fans');
		$zans 		= (int)$request->input('praise_nums');

		if(!$unionid || !$backend){
			return $this->error('Please submit the necessary information');
		}

		$had 		= TiktokDarren::where('account', $unionid)->first();
		if($had){
			if($had->account_id != $user->id){
				return $this->error('tiktok account has been added by other users');
			}elseif($had->status >= 0){
				$msg 	= $had->status == 0 ? 'Your tiktok account is under review' : 'Your tiktok account already exists';
				return $this->error($msg);
			}
		}

		$hads 		= TiktokDarren::where('account_id', $user->id)->where('status', '>', -10)->count();
		if($hads >= 30){
			return $this->error('Each account may not add more than 30 tiktok');
		}
		$backendUrl 	= $backend->store('backend/'.$user->id, 'admin');

		$row 		= new TiktokDarren;
		$row->union_id 	= $unionid;
		$row->nickname 	= $nickname;
		$row->fans 		= $fans;
		$row->zans 		= $zans;
		$row->screenshot= $backendUrl;
		$row->addtime 	= time();
		$row->account_id= $user->id;

		if($row->save()){
			return $this->success(null, 'Added successfully, waiting for review');
		}
		return $this->error('Failed to add');
	}

	//删除tiktok账号
	public function remove(Request $request){
		$user 		= $request->get('_user');
		$id 		= $request->input('id');

		$row 		= TiktokDarren::find($id);
		if(!$row || $row->account_id != $user->id){
			return $this->success(null, 'Deleted successfully');
		}

		if($row->delete()){
			if($row->screenshot){
				Storage::disk('admin')->delete($row->screenshot);
			}
			return $this->success(null, 'Deleted successfully');
		}
		return $this->error('Failed to delete');
	}
}
