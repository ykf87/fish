<?php

namespace App\Admin\Actions\Tiktok;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Encore\Admin\Facades\Admin;
use App\Tiktok\Shop;
use Illuminate\Support\Arr;

use App\Models\TiktokShop;

class Getproduct extends RowAction
{
    public $name = '同步商品';
    public function dialog(){
        $this->confirm('确定同步?');
    }

    public function handle(Model $form){
        $shopId     = '7494083165931603338';
        $rs         = $form->checkAccess(Admin::user()->id);
        if($rs !== true){//需要重新登录
            return $this->response()->error('授权已过期,请重新授权!')->refresh();
        }
        $shop       = new Shop;
        $shop->ProductLists($form->access_token, $shopId);
    }

}