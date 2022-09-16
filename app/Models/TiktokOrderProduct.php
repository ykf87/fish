<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokOrderProduct extends Model{
	use HasFactory;
	public $timestamps = false;

	public static function list($addtime = 1){
		$obj 		= self::from('tiktok_order_products as op')->
						selectRaw('p.id as id')->
						selectRaw('op.product_id as product_id')->
						selectRaw('p.images as image')->
						selectRaw('p.name as title')->
						selectRaw('op.sku_sale_price as unit_price')->
						selectRaw('p.commission as commission_ratio')->
						selectRaw('p.commissioned as accumulated_commission')->
						selectRaw('p.currency as currency')->
						selectRaw('sum(op.sku_sale_price) as cumulative_sales')->
						rightJoin('tiktok_products as p', 'p.pid', '=', 'op.product_id')->where('op.addtime', '>=', $addtime);
		return $obj;

		// return self::from('tiktok_order_products as op')->select('p.id', 'op.product_id', 'p.images as image', 'p.name as title', 'op.sku_sale_price as unit_price', 'p.commission as commission_ratio', 'p.commissioned as accumulated_commission', 'p.currency')->selectRaw('sum(op.sku_sale_price) as cumulative_sales')->rightJoin('tiktok_products as p', 'p.pid', '=', 'op.product_id')->where('op.addtime', '>=', $addtime)->offset(($page-1)*$limit)->limit($limit);
	}
}
