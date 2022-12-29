<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CourseOrder;
use App\Models\User;
use App\Globals\TimeFormat;

class Commission extends Model{
	use HasFactory;
	public $timestamps	= false;
	private static $rechargeRatio 	= [//分佣比例,基数为下级实际金额
		0.5,
		0.1,
		0.2
	];
	public static $status 			= [
		-1 	=> '无效',
		0 	=> '禁止提现',
		1 	=> '可提现',
	];
	public static $statusLabel		= [
		-1	=> 'danger',
		0 	=> 'warning',
		1 	=> 'success',
	];
	private static $max 			= 3;//最高分佣层级,0为不限制
	/**
	 * 计算用户积分充值后佣金分配
	 * 此次仅计算各用户获得佣金金额,不执行数据库操作
	 * @param $user Model 	付款用户实例
	 * @param $price float 	实际可分佣金额, 必须换算成美元
	 * @param $orderid int 	订单id
	 * @param $curr string 	$price对应的货币
	 * @return array
	 */
	public static function recharge(CourseOrder $order){
		if(self::where('orderid', $order->id)->count() > 0){
			return false;
		}
		if($order->status != 20 || !$order->paytime){
			return false;
		}

		$user 		= User::find($order->uid);
		if(!$user){
			return false;
		}
		$users 		= explode(',', $user->relation);
		$usersObj 	= User::whereIn('id', $users)->get();

		$userObjArr = [];
		foreach($usersObj as $item){
			$userObjArr[$item->id] 	= $item;
		}

		$users 		= array_reverse($users);
		$resp 		= [];
		$fromuid 	= $user->id;
		$now 		= time();
		$price 		= $order->total;
		$upbalances = [];

		$commcount 	= 0;
		foreach($users as $index => $item){
			if(!isset($userObjArr[$item])){
				$commcount++;
				continue;
			}
			$u 			= $userObjArr[$item];

			if(self::$max > 0 && $commcount >= self::$max){
				break;
			}
			if(!isset(self::$rechargeRatio[$commcount])){
				$ratio 	= end(self::$rechargeRatio);
			}else{
				$ratio 	= self::$rechargeRatio[$commcount];
			}

			$resp[] 	= [
				'uid'			=> $u->id,
				'buyuid'		=> $order->uid,
				'fromuid'		=> $fromuid,
				'commbase'		=> $price,
				'orderid'		=> $order->id,
				'total'			=> $order->total,
				'ratio'			=> $ratio,
				'geted'			=> $price * $ratio,
				'status'		=> $u->status == 1 ? 1 : 0,
				'addtime'		=> $now,
			];
			$price 		*= $ratio;
			$fromuid 	= $u->id;
			$commcount++;
		}
		if(count($resp) > 0){
			if(!self::insert($resp)){
				return false;
			}
		}
		return true;
	}

	public function getAddtimeAttribute($val){
		return $val ? TimeFormat::output($val) : null;
	}
}
