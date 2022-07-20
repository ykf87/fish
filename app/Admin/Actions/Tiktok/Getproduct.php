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

class Getproduct extends RowAction{
    public $name = '拉取商品';
    public function dialog(){
        $this->confirm('拉取商品只会拉取新的产品,不会更改原有的产品.确定拉取?');
    }

    public function handle(Model $form){
        set_time_limit(0);
        $shopId     = $form->shop_id;
        $model      = TiktokAccount::find($form->account_id);
        $rs         = $model->checkAccess(Admin::user()->id);
        if($rs !== true){//需要重新登录
            return $this->response()->error('授权已过期,请重新授权登录!')->refresh();
        }
        $shop       = new Shop;
        $page       = 1;
        $limit      = 100;
        $list       = $shop->ProductLists($model->access_token, $shopId, $page, $limit);
        if(is_string($list) || !isset($list['products'])){
            return $this->response()->error($list)->refresh();
        }
        $products   = $list['products'];
        TiktokProduct::addFromTiktok($products, $form->id, $model->id);

        $total      = $list['total'] ?? 1;
        $pages      = ceil($total / $limit);
        if($pages > $page){
            $start  = $page+1;
            for(;$start <= $pages; $start++){
                $list    = $shop->ProductLists($model->access_token, $shopId, $start, $limit);
                if(is_string($list) || !isset($list['products'])){
                    return $this->response()->error($list)->refresh();
                }
                TiktokProduct::addFromTiktok($list['products'], $form->id, $model->id);
            }
        }

        return $this->response()->success('产品拉取完成!')->refresh();
    }
}