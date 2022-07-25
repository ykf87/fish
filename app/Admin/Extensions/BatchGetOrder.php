<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Facades\Admin as uadmin;

use App\Models\TiktokAccount;
use App\Models\TiktokShop;

class BatchGetOrder extends AbstractTool{
	protected function script(){
        $admin_id       = uadmin::user()->id;
        $accounts       = TiktokAccount::where('aid', $admin_id)->pluck('seller_name', 'id')->toArray();
        $shops          = TiktokShop::where('aid', $admin_id)->pluck('shop_region', 'id')->toArray();
        // $html           = str_replace("\r\n", '', view('admin.tiktok.setcomm', [
        //     'accounts'  => $accounts,
        //     'shops'     => $shops,
        // ]));
        $html           = view('admin.tiktok.setcomm', [
            'accounts'  => $accounts,
            'shops'     => $shops,
        ]);
        uadmin::html(response($html)->getContent());
        return <<<EOT
$('.syncorders').click(function () {
    layer.confirm('同步所有订单?', function(){
        layer.msg('可以了!');
    });
    return false;
});


EOT;
    }

    public function render()
    {
        Admin::headerJs(asset('layer/layer.js'));
        Admin::script($this->script());

        return view('admin.tiktok.getorders');
    }
}