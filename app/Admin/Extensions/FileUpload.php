<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Form\Field;

class FileUpload extends Field
{
    //protected $view = 'admin.file_upload';

    protected function script(){

        $url = '/file_upload.html';

        return <<<EOT

$('.uploadFile').click(function () {
    layer.open({
        type: 2,
        area: ['300px', '220px'],
        title: '上传视频',
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
        return view('admin.file_upload_button');
    }
}
