<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\TiktokOrderProduct;
use App\Models\TiktokProduct;
use Illuminate\Support\Arr;
class TiktokOrder extends Model{
	use HasFactory;
    public $timestamps = false;
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

	public function pros(){
		return $this->hasMany(TiktokOrderProduct::class, 'id');
	}

	public function updateOrder(array $order){
		// print_r($order);
		$this->payment 			= $order['payment_method'];
		$this->shipment 		= $order['shipping_provider'] ?? null;
		$this->addtime 			= (int)($order['create_time'] / 1000);
		$this->paytime 			= isset($order['paid_time']) ? (int)($order['paid_time'] / 1000) : null;
		$this->remark 			= $order['buyer_message'];
		$this->currency 		= $order['payment_info']['currency'];
		$this->sub_total 		= $order['payment_info']['sub_total'];
		$this->shipping_fee 	= $order['payment_info']['shipping_fee'];
		$this->total_amount 	= $order['payment_info']['total_amount'];
		$this->resion 			= $order['recipient_address']['region'];
		$this->region_code 		= $order['recipient_address']['region_code'];
		$this->state 			= $order['recipient_address']['state'];
		$this->city 			= $order['recipient_address']['city'];
		$this->fulladdress 		= $order['recipient_address']['full_address'];
		$this->name 			= $order['recipient_address']['name'];
		$this->phone 			= $order['recipient_address']['phone'];
		$this->buyer_uid 		= $order['buyer_uid'];

		$orderProducts 			= [];
		$getProducts 			= Arr::pluck($order['item_list'], 'product_id');
		$dbres 					= TiktokProduct::whereIn('pid', $getProducts)->get();
		$dbProducts 			= [];
		foreach($dbres as $item){
			$dbProducts[$item->pid]	= $item;
		}
		$orderComm 				= 0;
		foreach($order['item_list'] as $item){
			$rrr	= [
				'id' 			=> $this->id,
				'product_id' 	=> $item['product_id'],
				'sku_id'		=> $item['sku_id'],
				'sku_name'		=> $item['sku_name'],
				'quantity'		=> $item['quantity'],
				'sku_image'		=> $item['sku_image'],
				'sku_sale_price'=> $item['sku_sale_price'],
				'addtime'		=> $this->addtime,
				'commissioned'	=> null,
			];
			if(isset($dbProducts[$item['product_id']]) && $dbProducts[$item['product_id']]->commission > 0 && $dbProducts[$item['product_id']]->commission < 1){
				$comm 					= $item['sku_sale_price'] * $dbProducts[$item['product_id']]->commission;
				$orderComm 				+= $comm;
				$rrr['commissioned']	= $comm;
			}
			$orderProducts[] 	= $rrr;
			$this->cover 		= $item['sku_image'];
			$this->pro_name		= isset($dbProducts[$item['product_id']]) ? $dbProducts[$item['product_id']]->name : $item['sku_name'];
		}
		$this->pro_num 			= count($orderProducts);
		$this->commission 		= $orderComm;
		$this->commission_rate 	= sprintf('%0.2f', $this->commission / $this->total_amount);
		DB::transaction(function () use($orderProducts) {
			$this->save();
			TiktokOrderProduct::insert($orderProducts);
		});
	}

	//订单列表
	public function list($page, $limit){
		if($page < 1){
			$page 	= 1;
		}
		if($limit < 1){
			$limit 	= 20;
		}
		return self::select('id', 'order_id', 'cover', 'pro_name as name', 'addtime as time', 'total_amount as payment', 'commission_rate as rate', 'commission', 'currency')->where('addtime', '>', 0)->orderByDesc('addtime')->offset(($page-1)*$limit)->limit($limit);
	}

	public function getCommissionAttribute($val){
		if($val < 0.01){
			$val 	= 0.01;
		}
		return $val;
	}

	public function getCommissionRateAttribute($val){
		$val 	*= 100;
		$val 	= ceil($val);
		if($val < 1){
			$val 	= 1;
		}
		return $val . '%';
	}

	public function getRateAttribute($val){
		$val 	*= 100;
		$val 	= ceil($val);
		if($val < 1){
			$val 	= 1;
		}
		return $val . '%';
	}
}
