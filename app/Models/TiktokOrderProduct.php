<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokOrderProduct extends Model{
	use HasFactory;
	public $timestamps = false;

	public function list($page, $limit){
		if($page < 1){
			$page 	= 1;
		}
		if($limit < 1){
			$limit 	= 20;
		}

		// self::select('op.id', 'op.product_id', 'op.sku_image as image', 'op.sku_name as title', 'op.sku_sale_price as unit_price', 'commission as commission_ratio', 'gmv as cumulative_sales', 'commissioned as accumulated_commission', 'currency')->where('addtime', '>', 0)->orderByDesc('gmv')->offset(($page-1)*$limit)->limit($limit);
	}
}
