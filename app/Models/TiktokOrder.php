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
		$this->addtime 			= $order['create_time'] / 1000;
		$this->paytime 			= $order['paid_time'] / 1000;
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
		$dbProducts 			= TiktokProduct::whereIn('pid', $getProducts)->pluck('commission', 'pid')->toArray();
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
			];
			if(isset($dbProducts[$item['product_id']]) && $dbProducts[$item['product_id']] > 0 && $dbProducts[$item['product_id']] < 1){
				$rrr['commissioned']		= $item['sku_sale_price'] * $dbProducts[$item['product_id']];
			}
			$orderProducts[] 	= $rrr;
		}
		$this->pro_num 			= count($orderProducts);
		DB::transaction(function () use($orderProducts) {
			$this->save();
			TiktokOrderProduct::insert($orderProducts);
		});
	}
}
