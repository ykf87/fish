<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TiktokAddress;

class AddressController extends Controller{
	//地址列表
	public function index(Request $request){
		$user 		= $request->get('_user');
		$page 		= (int)$request->input('page');
		$limit 		= (int)$request->input('limit');
		if($page < 1){
			$page 	= 1;
		}
		if($limit < 1){
			$limit 	= 20;
		}

		return $this->success(TiktokAddress::where('uid', $user->id)->orderByDesc('default')->orderByDesc('addtime')->offset(($page-1)*$limit)->limit($limit)->get(), '');
	}

	//添加地址
	public function add(Request $request){
		$user 		= $request->get('_user');
		$name 		= trim($request->input('name'), '');
		$phone 		= trim($request->input('phone'), '');
		$country 	= (int)$request->input('country');
		$address 	= trim($request->input('address'), '');

		if(!$name){
			return $this->error('Please fill in your name');
		}
		if(!$phone){
			return $this->error('Please fill in your phone number');
		}
		if(!$country){
			return $this->error('Please select a country');
		}
		if(!$address){
			return $this->error('Please fill in your full address');
		}

		$had 	= TiktokAddress::where('uid', $user->id)->count();
		if($had >= 5){
			return $this->error('Fill in up to 5 shipping addresses');
		}

		$addr 			= new TiktokAddress;
		$addr->uid 		= $user->id;
		$addr->name 	= $name;
		$addr->phone 	= $phone;
		$addr->country 	= $country;
		$addr->address 	= $address;
		$addr->addtime 	= time();
		if(!$addr->save()){
			return $this->error('Address addition failure');
		}
		return $this->success(null, 'Address added successfully');
	}

	//删除地址
	public function remove(Request $request){
		$user 	= $request->get('_user');
		$id 	= (int)$request->input('id');

		if(TiktokAddress::where('uid', $user->id)->where('id', $id)->delete()){
			return $this->success(null, 'Address deleted successfully');
		}
		return $this->error('Address deletion failure');
	}

	//设置默认地址
	public function default(Request $request){
		$user 	= $request->get('_user');
		$id 	= $request->input('id');

		$row 	= TiktokAddress::find($id);
		if(!$row || $row->uid != $user->id){
			return $this->error('Address does not exist');
		}
		if($row->default == 1){
			return $this->success(null, 'Default address set successfully');
		}
		TiktokAddress::where('uid', $user->id)->update(['default' => 0]);
		$row->default 	= 1;
		if($row->save()){
			return $this->success(null, 'Default address set successfully');
		}
		return $this->error('Default address setting failed');
	}
}
