<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

use App\Models\TiktokProduct;
use App\Models\TiktokCategory;
use App\Models\TiktokUserCollection;
use App\Models\TiktokSample;
use App\Models\TiktokDarren;
use App\Models\TiktokProductsResion;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
	//选品首页
	public function index(Request $request)
	{
		$cateLists 		= TiktokCategory::where('parent', 0)->pluck('name', 'id');
		$list = Banner::all()->toArray();
		$banners = [];
		foreach ($list as $item) {

			$b = [
				'id'	=> $item['id'],
				'des'	=> $item['name'],
				'image'	=>  Storage::disk('admin')->url($item['image']),
				'url'	=> $item['url'],
			];

			$banners[] = $b;
		}

		return $this->success(['category_lists' => $cateLists, 'banner' => $banners]);
	}

	//产品首页
	public function options(Request $request)
	{
		$q 			= trim($request->input('search'));
		$c 			= (int) $request->input('category');
		$s 			= (int) $request->input('sort');
		$page		= (int) $request->input('page');
		$limit		= (int) $request->input('limit');
		$is_samples	= $request->input('is_samples');

		$tp 		= new TiktokProduct;

		return [
			'code' => 200,
			'msg' => 'Success',
			'data' => $tp->frontList($page, $limit, $q, $c, $s, $is_samples)
		];
	}

	//详情
	public function detail(Request $request)
	{
		$user 		= $request->get('_user');
		$id 		= $request->input('id');
		if (!$id) {
			return $this->error('');
		}
		$row 		= TiktokProduct::detail($id);
		if (!$row) {
			return $this->error('');
		}
		$row->banner 	= explode(',', $row->banner);
		switch ($row->currency) {
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
		if ($user) {
			$row->is_collection 	= TiktokUserCollection::where('id', $user->id)->where('pid', $id)->first() ? true : false;
		}
		$region = TiktokProductsResion::where('pid', $id)->first();
		if ($region) {
			$reg = $region->resion;
		}
		$row->product_link     	= 'https://shop.tiktok.com/view/product/' . $row->pid . '?region=' . $reg . '&locale=en';

		return $this->success($row);
	}

	//收藏列表
	public function collects(Request $request)
	{
		$user 	= $request->get('_user');
		$page 	= (int) $request->input('page', 1);
		$limit 	= (int) $request->input('limit', 20);
		if ($page < 1) $page = 1;
		if ($limit < 1) $limit = 20;

		$total 	= TiktokUserCollection::list($user->id)->count();
		$list 	= TiktokUserCollection::list($user->id)->orderByDesc('c.addtime')->offset(($page - 1) * $limit)->limit($limit)->get();
		$arr 	= [
			'total_limit'	=> $total,
			'page'			=> $page,
			'limit'			=> $limit,
			'collection_lists'	=> $list,
		];
		return $this->success($arr, '');
	}

	//收藏/取消收藏
	public function collection(Request $request)
	{
		$user 	= $request->get('_user');
		$coloct	= $request->input('is_collection');

		if ($coloct) {
			return $this->collect($request);
		} else {
			return $this->uncollect($request);
		}
	}

	//收藏商品
	public function collect(Request $request)
	{
		$user 	= $request->get('_user');
		$pid 	= $request->input('id');

		$had 	= TiktokUserCollection::where('id', $user->id)->where('pid', $pid)->first();
		if (!$had) {
			$tc 		= new TiktokUserCollection;
			$tc->id 		= $user->id;
			$tc->pid 		= $pid;
			$tc->addtime 	= time();
			if (!$tc->save()) {
				return $this->error('Collection failure');
			}
		}
		return $this->success('', 'Collection success');
	}

	//取消商品收藏
	public function uncollect(Request $request)
	{
		$user 	= $request->get('_user');
		$pid 	= $request->input('id');

		$had 	= TiktokUserCollection::where('id', $user->id)->where('pid', $pid)->first();
		if ($had) {
			if (!TiktokUserCollection::where('id', $user->id)->where('pid', $pid)->delete()) {
				return $this->error('Failed to cancel collection');
			}
		}
		return $this->success('', 'Successfully uncollected');
	}

	//领样申请
	public function apply(Request $request)
	{
		$user 		= $request->get('_user');
		$id 		= (int) $request->input('id');
		$address 	= (int) $request->input('address_id');
		$tiktok 	= (int) $request->input('tiktok_id');
		$notes 		= trim($request->input('notes'));

		if (!$id) {
			return $this->error('Please select a product');
		}
		if (!$tiktok) {
			return $this->error('Please select tiktok account');
		}
		if (!$address) {
			return $this->error('Please select the shipping address');
		}

		$had 	= TiktokSample::where('account_id', $user->id)->where('status', 0)->get();
		if (count($had) >= 10) { //总的待审核申领不能超过10个
			return $this->error('Simultaneous application of samples can not exceed 10');
		}
		foreach ($had as $item) { //同一个样品在待审核状态下无法重复申领
			if ($item->pid == $id) {
				return $this->error('You have requested a sample, please wait for review');
			}
		}

		$pro 	= TiktokProduct::find($id);
		if (!$pro) {
			return $this->error('The product you applied for does not support sending samples');
		} elseif ($pro->is_samples != 1) {
			return $this->error('Samples are not available for this product');
		}

		$daren 	= TiktokDarren::find($tiktok);
		if (!$daren || $daren->account_id != $user->id) {
			return $this->error('Your tiktok account does not exist');
		}
		if ($daren->status != 1) {
			return $this->error('Your tiktok account is under review or has been rejected');
		}

		if ($pro->fans > 0 && $daren->fans < $pro->fans) {
			return $this->error('Your tiktok account does not meet the requirements for sending samples');
		}
		if ($pro->zans > 0 && $daren->zans < $pro->zans) {
			return $this->error('Your tiktok account does not meet the requirements for sending samples');
		}

		$row 					= new TiktokSample;
		$row->account_id 		= $user->id;
		$row->darren_id			= $tiktok;
		$row->pid				= $id;
		$row->product_id		= $pro->pid;
		$row->product_name		= $pro->name;
		$row->product_image		= $pro->images ? explode(',', $pro->images)[0] : '';
		$row->addtime			= time();
		$row->remark			= $notes;

		if ($row->save()) {
			return $this->success(null, 'Sample request successful');
		}
		return $this->error('Sample Request Failed');
	}
}
