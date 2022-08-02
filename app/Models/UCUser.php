<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UCUser extends Model{
	use HasFactory;
    public $timestamps = false;
	protected $connection 	= 'ucenter';
	protected $table 		= 'users';
	public static $sex 		= [
		'未知',
		'男',
		'女',
		'变性'
	];
	public static $sex_label 	= [
		'default',
		'info',
		'danger',
		'default'
	];
	public static $status 	= [
		-2 			=> '封号',
		-1			=> '注销',
		0 			=> '未激活',
		1 			=> '正常'
	];
	public static $status_label 	= [
		-2 			=> 'danger',
		-1			=> 'warning',
		0 			=> 'info',
		1 			=> 'success'
	];

	public function getPidAttribute($val){
		if($val > 0){
			return $val;
		}
		return null;
	}
	public function setPidAttribute($val){
		$val 		= trim($val);
		$chain 		= '';
		if($val && strlen($val) == 8){
			if($this->attributes['pid'] > 0){
				return false;
			}
			$res 	= self::where('invite', $val)->first();
			if($res){
				$val 	= $res->id;
				$chain 	= $res->chain;
			}
		}
		if(!is_numeric($val)){
			return false;
		}
		$this->attributes['pid'] 		= $val;
		$this->attributes['chain']		= $chain ? $chain . ',' . $val : $val;
	}
}
