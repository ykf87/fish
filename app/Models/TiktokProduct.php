<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Facades\Admin;
use App\Models\TiktokProductsSku;
use Illuminate\Support\Arr;

class TiktokProduct extends Model{
	use HasFactory;

	public static function addFromTiktok($products, $shopid, $accountid){
		$dbproids 		= self::where('shop_id', $shopid)->pluck('id', 'pid')->toArray();
		$getproids 		= Arr::pluck($products, 'id', 'id');
		$adminId 		= Admin::user()->id;

		$inserts 		= [];
		$insertSkus 	= [];
		foreach($products as $item){
			if(isset($dbproids[$item['id']])){
				continue;
			}
			$minprice 	= 999999999;
			$maxprice 	= 0;
			$stock 		= 0;
			$curr 		= '';
			if(isset($item['skus'])){
				foreach($item['skus'] as $sitem){
					$curr 		= $sitem['price']['currency'];
					if($minprice > $sitem['price']['price_include_vat']){
						$minprice 	= $sitem['price']['price_include_vat'];
					}
					if($maxprice < $sitem['price']['price_include_vat']){
						$maxprice 	= $sitem['price']['price_include_vat'];
					}
					$stock 		+= $sitem['stock_infos'][0]['available_stock'];


					$insertSkus[$item['id']]	= [
						'sid'				=> $sitem['id'],
						'currency'			=> $sitem['price']['currency'],
						'original_price'	=> $sitem['price']['original_price'],
						'price_include_vat'	=> $sitem['price']['price_include_vat'],
						'seller_sku'		=> $sitem['seller_sku'],
						'stock'				=> $sitem['stock_infos'][0]['available_stock'],
					];
				}
			}
			$arr 		= [
				'aid'			=> $adminId,
				'account_id'	=> $accountid,
				'shop_id'		=> $shopid,
				'pid'			=> $item['id'],
				'name'			=> $item['name'],
				'create_time'	=> $item['create_time'],
				'status'		=> $item['status'],
				'maxprice'		=> $maxprice,
				'minprice'		=> $minprice,
				'currency'		=> $curr,
				'stocks'		=> $stock,
			];
			$inserts[] 					= $arr;
		}
		if(count($inserts) < 1){
			return;
		}
		self::insert($inserts);
		$res 	= self::whereIn('pid', array_keys($insertSkus))->pluck('id', 'pid')->toArray();
		foreach($insertSkus as $pid => &$arr){
			if(isset($res[$pid])){
				$arr['pid']		= $res[$pid];
			}else{
				unset($insertSkus[$pid]);
			}
		}
		if(count($insertSkus) > 0){
			TiktokProductsSku::insert($insertSkus);
		}
	}
}
