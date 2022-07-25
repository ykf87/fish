<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokOrder extends Model{
	use HasFactory;
	public static $status 	= [
		100		=> '待支付',
		111		=> '待发货',
		112		=> '待确认',
		114 	=> '部分发货',
		121		=> '配送中',
		122		=> '已签收',
		130		=> '确认收货',
		140		=> '取消'
	];
}
