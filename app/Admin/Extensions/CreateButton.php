<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Tools\AbstractTool;


class CreateButton extends AbstractTool{

    protected $param;

    public function __construct($param)
    {
        if (empty($param['fa_icon'])) {
            $param['fa_icon'] = 'fa-plus';
        }
        if (empty($param['btn_class'])) {
            $param['btn_class'] = 'btn btn-sm btn-success';
        }
        $this->param = $param;
    }
    public function render()
    {
        return view('admin.create_button', $this->param);
    }
}