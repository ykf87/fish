<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TiktokProduct;


class TiktokShop extends Model
{
	use HasFactory;
	public $timestamps = false;

	public static $type 	= [
		1	=> '跨境',
		2	=> '本地',
	];
	public static $region 		= [
		'GB'	=> '英国',
		'MY'	=> '马来',
		'PH'	=> '菲律宾',
		'SG'	=> '新加坡',
		'TH'	=> '泰国',
		'VN'	=> '越南'
	];


	public function product()
	{
		return $this->hasMany(TiktokProduct::class);
	}
}
