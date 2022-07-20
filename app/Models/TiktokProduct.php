<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Facades\Admin;
use App\Models\TiktokProductsSku;
use App\Models\TiktokProductsResion;
use App\Models\TiktokShop;
use App\Models\TiktokAccount;
use Illuminate\Support\Arr;

class TiktokProduct extends Model{
	use HasFactory;
	public $timestamps = false;
	public static $status 	= [
		1	=> '草稿',
		2	=> '待定',
		3	=> '初创',
		4	=> '上线',
		5	=> '卖家停用',
		6	=> '平台停用',
		7	=> '冻结',
		8	=> '删除',
	];
	public static $statusLabel 	= [
		1	=> 'default',
		2	=> 'primary',
		3	=> 'info',
		4	=> 'success',
		5	=> 'warning',
		6	=> 'warning',
		7	=> 'danger',
		8	=> 'danger',
	];

	public static function addFromTiktok($products, $shopid, $accountid){
		$dbproids 		= self::where('shop_id', $shopid)->pluck('id', 'pid')->toArray();
		$getproids 		= Arr::pluck($products, 'id', 'id');
		$adminId 		= Admin::user()->id;

		$inserts 		= [];
		$insertSkus 	= [];
		$sellresions 	= [];
		$pids 			= [];
		foreach($products as $item){
			if(isset($dbproids[$item['id']])){
				continue;
			}
			$pids[] 	= $item['id'];
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


					$insertSkus[$item['id']][]	= [
						'sid'				=> $sitem['id'],
						'currency'			=> $sitem['price']['currency'],
						'original_price'	=> $sitem['price']['original_price'],
						'price_include_vat'	=> $sitem['price']['price_include_vat'],
						'seller_sku'		=> $sitem['seller_sku'],
						'stock'				=> $sitem['stock_infos'][0]['available_stock'],
					];
				}
			}else{
				$minprice 	= 0;
			}
			if(isset($item['sale_regions'])){
				foreach($item['sale_regions'] as $rsion){
					$sellresions[$item['id']][] 	= ['resion' => $rsion];
				}
			}
			if($maxprice < $minprice){
				$minprice 	= 0;
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
			// self::insert($arr);
		}

		if(count($inserts) > 1){
			self::insert($inserts);

			$res 	= self::whereIn('pid', $pids)->pluck('id', 'pid')->toArray();
			$isk 	= [];
			$isr 	= [];
			// print_r($insertSkus);
			// print_r($sellresions);
			foreach($insertSkus as $pid => $arr){
				if(isset($res[$pid])){
					foreach($arr as $bbb){
						$bbb['pid']	= $res[$pid];
						$isk[] 		= $bbb;
					}
				}
			}
			if(count($isk) > 0){
				TiktokProductsSku::insert($isk);
			}
			foreach($sellresions as $pid => $vals){
				if(isset($res[$pid])){
					foreach($vals as $bbb){
						$bbb['pid']	= $res[$pid];
						$isr[] 		= $bbb;
					}
				}
			}
			if(count($isr) > 0){
				TiktokProductsResion::insert($isr);
			}
		}
		

		TiktokShop::where('id', $shopid)->update(['product_number' => self::where('shop_id', $shopid)->count()]);
		TiktokAccount::where('id', $accountid)->update(['product_num' => self::where('account_id', $accountid)->count()]);
	}
}
