<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokDarren extends Model{
	use HasFactory;
	public $timestamps = false;

	public static function lists($uid, $q = ''){
		$obj 		= self::select('id', 'avatar as image', 'nickname as name', 'fans', 'zans as praise_nums')->where('account_id', $uid)->orderByDesc('id');
		if($q){
			$obj 	= $obj->where('nickname', 'like', "%$q%");
		}
		return $obj;
	}
}
