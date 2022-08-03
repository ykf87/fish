<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TiktokProduct;
use App\Models\TiktokCategory;
use App\Models\TiktokUserCollection;
use App\Models\TiktokSample;
use App\Models\TiktokDarren;

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
		$user 		= $request->get('_user');
		$id 		= $request->input('id');
		$row 		= TiktokProduct::detail($id);
		if(!$row){
			return $this->error('');
		}
		$row->banner 	= explode(',', $row->banner);
		switch($row->currency){
			case 'SGD':
				$row->left_icon 	= 'S$';
				break;
			case 'GBP':
				$row->left_icon 	= '£';
				break;
			case 'MYR':
				$row->left_icon 	= 'RM';
				break;
			case 'PHP':
				$row->left_icon 	= 'PHP';
				break;
			case 'THB':
				$row->left_icon 	= 'THB';
				break;
			case 'VND':
				$row->left_icon 	= 'VND';
				break;
			default:
				$row->left_icon 	= '$';
		}
		$row->benefits 	= '';
		$row->delivery_place 	= 'CN';
		$row->express_company 	= 'DHL';
		$row->delivery_time 	= 'in 48 hours';
		$row->is_collection 	= false;
		if($user){
			$row->is_collection 	= TiktokUserCollection::where('id', $user->id)->where('pid', $id)->first() ? true : false;
		}
		return $this->success($row);
	}

	//收藏商品
	public function collect(Request $request){
		$user 	= $request->get('_user');
		$pid 	= $request->get('id');

		$had 	= TiktokUserCollection::where('id', $user->id)->where('pid', $pid)->first();
		if(!$had){
			$tc 		= new TiktokUserCollection;
			$tc->id 		= $user->id;
			$tc->pid 		= $pid;
			$tc->addtime 	= time();
			if(!$tc->save()){
				return $this->error('Collection failure');
			}
		}
		return $this->success('', 'Collection success');
	}

	//取消商品收藏
	public function uncollect(Request $request){
		$user 	= $request->get('_user');
		$pid 	= $request->get('id');

		$had 	= TiktokUserCollection::where('id', $user->id)->where('pid', $pid)->first();
		if($had){
			if(!TiktokUserCollection::where('id', $user->id)->where('pid', $pid)->delete()){
				return $this->error('Failed to cancel collection');
			}
		}
		return $this->success('', 'Successfully uncollected');
	}

	//领样申请
	public function apply(Request $request){
		$user 		= $request->get('_user');
		$id 		= (int)$request->input('id');
		$address 	= (int)$request->input('address_id');
		$tiktok 	= (int)$request->input('tiktok_id');
		$notes 		= trim($request->input('notes'));

		if(!$id){
			return $this->error('Please select a product');
		}
		if(!$tiktok){
			return $this->error('Please select tiktok account');
		}
		if(!$address){
			return $this->error('Please select the shipping address');
		}

		$had 	= TiktokSample::where('account_id', $user->id)->where('status', 0)->get();
		if(count($had) >= 10){//总的待审核申领不能超过10个
			return $this->error('Simultaneous application of samples can not exceed 10');
		}
		foreach($had as $item){//同一个样品在待审核状态下无法重复申领
			if($item->pid == $id){
				return $this->error('You have requested a sample, please wait for review');
			}
		}

		$pro 	= TiktokProduct::find($id);
		if(!$pro){
			return $this->error('The product you applied for does not support sending samples');
		}

		$daren 	= TiktokDarren::find($tiktok);
		if(!$daren || $daren->account_id != $user->id){
			return $this->error('Your tiktok account does not exist');
		}

		if($pro->fans > 0 && $daren->fans < $pro->fans){
			return $this->error('Your tiktok account does not meet the requirements for sending samples');
		}
		if($pro->zans > 0 && $daren->zans < $pro->zans){
			return $this->error('Your tiktok account does not meet the requirements for sending samples');
		}

		$row 					= new TiktokSample;
		$row->account_id 		= $user->id;
		$row->darren_id			= $tiktok;
		$row->pid				= $id;
		$row->product_id		= $pro->pid;
		$row->product_name		= $pro->name;
		$row->product_image		= $images;
		$row->addtime			= time();
		$row->remark			= $notes;

		if($row->save()){
			return $this->success(null, 'Sample request successful');
		}
		return $this->error('Sample Request Failed');
	}
}
