<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

use App\Models\TiktokAccount;
use App\Models\TiktokShop;
use Encore\Admin\Facades\Admin;
use App\Tiktok\Shop;

class BatchUpdateProduct extends BatchAction{
	public $name = '批量更新';

	public function handle(Collection $collection){
		set_time_limit(0);
		$ids 		= [];
		$shop   	= new Shop;
		$shopids 	= [];
		foreach ($collection as $model) {
			$ids[$model->account_id][$model->shop_id][] 	= $model->pid;
			$shopids[$model->shop_id] 	= 1;
		}
		$shopId2TkShopId 	= TiktokShop::whereIn('id', array_keys($shopids))->pluck('shop_id', 'id')->toArray();
		foreach($ids as $accountid => $arr){
			$account 	= TiktokAccount::find($accountid);
			if($account){
				if($account->checkAccess(Admin::user()->id)){
					foreach($arr as $shopid => $getids){
						if(!isset($shopId2TkShopId[$shopid])){
							continue;
						}
						$sid 		= $shopId2TkShopId[$shopid];
						$res 		= $shop->ProductInfo($getids, $account->access_token, $sid);
						if($res !== true){
							return $this->response()->error($res)->refresh();
						}
					}
				}else{
					return $this->response()->error('更新的产品中有店铺授权已过期!')->refresh();
				}
			}
		}
		return $this->response()->success('更新请求已加入到队列!')->refresh();
	}

}