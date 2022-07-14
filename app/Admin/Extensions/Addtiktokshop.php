<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Facades\Admin as uadmin;

use App\Tiktok\Shop;

class Addtiktokshop extends AbstractTool{
	protected function script(){
        $shop   = new Shop();
        $url    = $shop->authurl(uadmin::user()->id);

        return <<<EOT

$('.addshopbtn').click(function () {
    // window.open('$url');
    layer.open({
        type: 2,
        area: ['60%', '90%'],
        content: '$url'
    });
    return false;
});


EOT;
    }

    public function render()
    {
        // Admin::headerJs(asset('layer/layer.js'));
        Admin::script($this->script());
        return view('admin.tiktok.add');
    }
}