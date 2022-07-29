<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Globals\Ens;
use Illuminate\Support\Arr;

use App\Models\TiktokProduct;
use App\Models\TiktokOrder;
use Illuminate\Support\Facades\DB;

class CallbackController extends Controller{
    //后台回调
	public function index(Request $request){
		echo '<center style="padding: 50px 0;">授权处理中.....</center>';
		$state      = $request->get('state');
        $code       = $request->get('code');
        if(!$code || !$state){
            return abort(404);
        }
        $str        = Ens::decrypt(base64_decode($state));
        $res        = json_decode($str, true);
        if(!isset($res['id']) || !isset($res['time'])){
            return abort(404);
        }
        if((time()-$res['time']) > 60000){
            exit('<script>window.parent.layer.closeAll();window.parent.layer.msg("授权超时!");</script>');
        }else{
        	exit('<script>window.parent.layer.load(1);window.parent.location="/admin/tiktok-account?code='.$code.'&aid='.$res['id'].'";</script>');
        }
	}

    //前端登录回调
    public function userLogin(Request $request){
        
    }

    //产品详情
    public function proinfo(Request $request){
        $data   = $request->input('data');
        if(!$data){
            return;
        }
        $data   = json_decode($data, true);
        $data   = $data['data'] ?? null;
        if(!$data){
            return;
        }

        TiktokProduct::updFromTiktok($data);
        // file_put_contents(__DIR__ . '/1.txt', $data['product_id']);
    }

    //订单详情
    public function orderinfo(Request $request){
        $data   = $request->input('data');
        if(!$data){
            return;
        }
        $data   = json_decode($data, true);
        $data   = $data['data'] ?? null;
        if(!$data || !isset($data['order_list'])){
            return;
        }

        $list       = $data['order_list'];
        $getOrders  = [];
        foreach($list as $item){
            $getOrders[$item['order_id']]   = $item;
        }

        $dbOrders   = TiktokOrder::whereIn('order_id', array_keys($getOrders))->get();
        foreach($dbOrders as $item){
            if(isset($getOrders[$item->order_id])){
                $item->updateOrder($getOrders[$item->order_id]);
            }
        }
    }

    //汇总一些数据
    public function aggregate(){
        //汇总产品销量
        $sql    = 'update tiktok_products set sales = 0, gmv = 0, commissioned = 0 where sales > 0';
        DB::unprepared($sql);
        $sql        = 'update tiktok_products as p inner join (select product_id as product_id, sum(quantity) as sales, sum(sku_sale_price) as gmv, sum(commissioned) as comm from tiktok_order_products group by product_id) as r on r.product_id = p.pid set p.sales = r.sales, p.gmv = r.gmv, p.commissioned = r.comm';
        DB::unprepared($sql);


        //汇总商店销量
        $sql    = 'update tiktok_shops set sales = 0, gmv = 0 where sales > 0';
        DB::unprepared($sql);
        $sql        = 'update tiktok_shops as s inner join (select shopid, count(id) as sales, sum(total_amount) as gmv from tiktok_orders group by shopid) as r on r.shopid = s.id set s.sales = r.sales, s.gmv = r.gmv';
        DB::unprepared($sql);
    }
}