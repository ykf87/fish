<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Facades\Admin as uadmin;

use App\Tiktok\Shop;

class CreateButton extends AbstractTool{

    public function render()
    {
        $data = [
            'button_name' => '新增视频',
            'url' => admin_url('tiktok-products-videos/create?pid=' . Request::input('pid')),
        ];
        return view('admin.create_button', $data);
    }
}