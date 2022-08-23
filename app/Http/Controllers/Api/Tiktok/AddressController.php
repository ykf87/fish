<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TiktokAddress;

class AddressController extends Controller
{
	//地址列表
	public function index(Request $request)
	{
		$user 		= $request->get('_user');
		$page 		= (int) $request->input('page');
		$limit 		= (int) $request->input('limit');
		$id 		= (int) $request->input('id');
		if ($id > 0) {
			$row 	= TiktokAddress::select('id', 'uid', 'name', 'phone as tel', 'country as country_id', 'country_name', 'city as city_id', 'city_name', 'address as detail', 'default as is_default')->find($id); //('id', $id)->first();
			if ($row && $row->uid == $user->id) {
				unset($row->uid);
				return $this->success($row);
			}
			return $this->error('Not found');
		}
		if ($page < 1) {
			$page 	= 1;
		}
		if ($limit < 1) {
			$limit 	= 20;
		}

		$list 		= TiktokAddress::select('id', 'name', 'phone as tel', 'country as country_id', 'country_name', 'city as city_id', 'city_name', 'address as detail', 'default as is_default')
			->where('uid', $user->id)->orderByDesc('default')->orderByDesc('addtime')->offset(($page - 1) * $limit)->limit($limit)->get();
		return $this->success($list, '');
	}

	//添加地址
	public function add(Request $request)
	{
		$user 		= $request->get('_user');
		$id 		= (int) $request->input('id');
		$name 		= trim($request->input('name'), '');
		$phone 		= trim($request->input('tel'), '');
		$country 	= (int) $request->input('country_id');
		$cname 		= $request->input('country_name');
		$city 		= (int) $request->input('city_id');
		$ctname		= $request->input('city_name');
		$address 	= trim($request->input('detail'), '');
		$default 	= $request->input('is_default');

		$successMsg 	= 'Address added successfully';
		if (!$id) {
			if (!$name) {
				return $this->error('Please fill in your name');
			}
			if (!$phone) {
				return $this->error('Please fill in your phone number');
			}
			if (!$country) {
				return $this->error('Please select a country');
			}
			if (!$address) {
				return $this->error('Please fill in your full address');
			}
			$had 	= TiktokAddress::where('uid', $user->id)->count();
			if ($had >= 10) {
				return $this->error('Fill in up to 10 shipping addresses');
			}

			$addr 					= new TiktokAddress;
			$addr->addtime 			= time();
			$addr->uid 				= $user->id;
		} else {
			$addr 					= TiktokAddress::find($id);
			if (!$addr) {
				return $this->error('Address does not exist');
			}
			$successMsg 			= 'Address updated successfully';
		}


		if ($name) {
			$addr->name 			= $name;
		}
		if ($phone) {
			$addr->phone 			= $phone;
		}
		if ($country) {
			$addr->country 			= $country;
			$addr->country_name 	= $cname;
		}
		if ($city) {
			$addr->city 			= $city;
			$addr->city_name 		= $ctname;
		}
		if ($address) {
			$addr->address 			= $address;
		}
		if ($default) {
			$addr->default 	= 1;
		}

		if (!$addr->save()) {
			return $this->error('Address addition failure');
		}
		if ($addr->default == 1) {
			TiktokAddress::where('default', 1)->where('id', '!=', $addr->id)->update(['default' => 0]);
		}
		return $this->success(null, $successMsg);
	}

	//修改地址
	public function editer(Request $request)
	{
		$user 		= $request->get('_user');

		$id 		= $request->input('id');
		$name 		= trim($request->input('name'), '');
		$phone 		= trim($request->input('phone'), '');
		$country 	= (int) $request->input('country');
		$address 	= trim($request->input('address'), '');

		$row 		= TiktokAddress::find($id);
		if (!$row || $row->uid != $user->id) {
			return $this->error('Address does not exist, please add it first');
		}
		$arr 		= [];
		if ($name && $row->name != $name) {
			$arr['name']	= $name;
		}
		if ($phone && $row->phone != $phone) {
			$arr['phone']	= $phone;
		}
		if ($country && $row->country != $country) {
			$arr['country']	= $country;
		}
		if ($address && $row->address != $address) {
			$arr['address']	= $address;
		}

		if (empty($arr)) {
			return $this->success(null, 'Modified successfully');
		}
		if (TiktokAddress::where('id', $row->id)->update($arr)) {
			return $this->success(null, 'Modified successfully');
		}
		return $this->error('Modification failure');
	}

	//删除地址
	public function remove(Request $request)
	{
		$user 	= $request->get('_user');
		$id 	= (int) $request->input('id');

		if (TiktokAddress::where('uid', $user->id)->where('id', $id)->delete()) {
			return $this->success(null, 'Address deleted successfully');
		}
		return $this->error('Address deletion failure');
	}

	//设置默认地址
	public function
	default(Request $request)
	{
		$user 	= $request->get('_user');
		$id 	= $request->input('id');

		$row 	= TiktokAddress::find($id);
		if (!$row || $row->uid != $user->id) {
			return $this->error('Address does not exist');
		}
		if ($row->default == 1) {
			return $this->success(null, 'Default address set successfully');
		}
		TiktokAddress::where('uid', $user->id)->update(['default' => 0]);
		$row->default 	= 1;
		if ($row->save()) {
			return $this->success(null, 'Default address set successfully');
		}
		return $this->error('Default address setting failed');
	}
}
