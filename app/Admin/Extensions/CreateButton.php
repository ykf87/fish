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
        $this->param = $param;
    }
    public function render()
    {
        return view('admin.create_button', $this->param);
    }
}