<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Facades\Admin as uadmin;

use App\Tiktok\Shop;
use App\Models\TiktokAccount;
use App\Models\TiktokShop;

class BatchComm extends AbstractTool{
	protected function script(){
        $admin_id       = uadmin::user()->id;
        $accounts       = TiktokAccount::where('aid', $admin_id)->pluck('seller_name', 'id')->toArray();
        $shops          = TiktokShop::where('aid', $admin_id)->pluck('shop_region', 'id')->toArray();
        $html           = str_replace("\r\n", '', view('admin.tiktok.setcomm', [
            'accounts'  => $accounts,
            'shops'     => $shops,
        ]));
        return <<<EOT
$('.addshopbtn').click(function () {
    layer.open({
        title: '根据以下条件设置产品佣金!',
        type: 1,
        area: ['40%', '80%'],
        content: '$html'
    });
    return false;
});


EOT;
    }

    public function render()
    {
        Admin::headerJs(asset('layer/layer.js'));
        Admin::script($this->script());

        return view('admin.tiktok.comm');
    }
}