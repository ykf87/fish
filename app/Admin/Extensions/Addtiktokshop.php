<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Facades\Admin as uadmin;

use App\Tiktok\Shop;

class Addtiktokshop extends AbstractTool{
	protected function script(){
        // $url = Request::fullUrlWithQuery(['gender' => '_gender_']);
        $shop   = new Shop();
        $url    = $shop->authurl(uadmin::user()->id);

        return <<<EOT

$('.addshopbtn').click(function () {
    window.open('$url');
    return false;
});

EOT;
    }

    public function render()
    {
        Admin::headerJs(asset('layer/layer.js'));
        Admin::script($this->script());
        // Admin::script('window.open("https://www.zhishukongjian.com");');

        // $options = [
        //     'all'   => 'All',
        //     'm'     => 'Male',
        //     'f'     => 'Female',
        // ];
        return view('admin.tiktok.add');
    }
}