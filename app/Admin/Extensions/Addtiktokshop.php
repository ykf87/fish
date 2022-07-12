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
        // $url    = 'https://fish.mini.zhishukongjian.com/api/tiktok/callback?code=onkizAAAAADTYf4kTd_6t3ZnOzeeXrxM1n3dTtjcZ7HAhpJmC_0nRjDo7rdU84Rnbt4pwURvyrzBVl0nKSmBKYcoyNgZHmGZR7b5iqe1vyXV40miXZePnUA_TRJn5OVZFjsbly9fYofQkMKVo2YMUfB9ECHrfVb_&state=YytjeGVLSXVtQ3RFRTJ4UlVNUlZaWWdRbHE2b0JLSzNEQkZNYkRUcEJlWT0';

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