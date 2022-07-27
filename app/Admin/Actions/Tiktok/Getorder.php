<?php

namespace App\Admin\Actions\Tiktok;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Encore\Admin\Facades\Admin;
use App\Tiktok\Shop;
use Illuminate\Support\Arr;

use App\Models\TiktokProduct;
use App\Models\TiktokAccount;
use App\Models\TiktokOrder;

class Getorder extends RowAction{
    public $name = '同步订单';
    public function dialog(){
        $this->confirm('同步订单,将更新所有订单状态,并拉取最新订单.同步的订单并不是完整的订单,确定同步?');
    }

    public function handle(Model $form){
        // if($form extends)
        set_time_limit(0);
        $admin_id   = Admin::user()->id;
        $shopId     = $form->shop_id;
        $model      = TiktokAccount::find($form->account_id);
        $rs         = $model->checkAccess(Admin::user()->id);
        if($rs !== true){//需要重新登录
            return $this->response()->error('授权已过期,请重新授权登录!')->refresh();
        }
        $shop       = new Shop;

// $res    = $shop->SyncOrderInfo(['576460901740611992', '576460901765384600'], $model->access_token, $shopId);
// dd($res);

        $list       = $shop->OrderLists($model->access_token, $shopId);
        if(!is_array($list)){
            return $this->response()->error($list)->refresh();
        }
        $orderIds       = Arr::pluck($list, 'order_id');
        $dborders       = [];
        $toktokOrders   = TiktokOrder::whereIn('order_id', $orderIds)->get()->toArray();
        foreach($toktokOrders as $item){
            $dborders[$item['order_id']] = $item;
        }

        $inarr      = [];
        $ccc        = 0;
        $uuu        = 0;
        $iii        = 0;
        $orderids   = [];
        foreach($list as $item){
            if(isset($dborders[$item['order_id']])){
                if(!$dborders[$item['order_id']]['addtime']){
                    $orderids[]         = $item['order_id'];
                }
                if($dborders[$item['order_id']]['status'] == $item['order_status']){
                    $ccc++;
                    continue;
                }
                TiktokOrder::where('order_id', $item['order_id'])->update(['order_status' => $item['order_status']]);
                $uuu++;
            }else{
                $inarr[]    = [
                    'aid'           => $admin_id,
                    'account_id'    => $form->account_id,
                    'shopid'        => $form->id,
                    'order_id'      => $item['order_id'],
                    'status'        => $item['order_status']
                ];
                $orderids[]         = $item['order_id'];
            }
        }
        $iii        = count($inarr);
        if($iii > 0){
            TiktokOrder::insert($inarr);
        }
        if(count($orderids)){
            $page   = 1;
            $limit  = 20;
            $pages  = ceil(count($orderids) / $limit);
            for(;$page<=$pages;$page++){
                $shop->SyncOrderInfo(array_slice($orderids, ($page-1)*$limit, $limit), $model->access_token, $shopId);
            }
        }


        return $this->response()->success("订单拉取完成,更新: $uuu 个, 新增: $iii 个, 未处理: $ccc 个")->refresh();
    }
}