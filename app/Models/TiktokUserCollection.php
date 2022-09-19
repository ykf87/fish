<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokUserCollection extends Model{
	use HasFactory;
	public $timestamps = false;

	public static function list($uid){
		$obj 	= TiktokUserCollection::from('tiktok_user_collections as c')
					->select('c.pid as product_id', 'p.currency', 'p.stocks as stock', 'p.images as image', 'p.name as title', 'p.minprice as unit_price', 'commission as commission_ratio')
					->leftJoin('tiktok_products as p', 'c.pid', '=', 'p.id')
					->where('c.id', $uid);
		return $obj;
	}

	public function getImageAttribute($val){
		return $val ? explode(',', $val)[0] : '';
	}
}
