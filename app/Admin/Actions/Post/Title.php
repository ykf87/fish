<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Title extends RowAction{
    public $name = '封号';

    public function dialog(){
        $this->confirm('确定封号?');
    }
    public function handle(Model $model){
        if($model->status < 0){
            return $this->response()->error('该账号非正常账号,无需封禁!');
        }
        $model->status      = -2;
        $model->singleid    = -1;
        if($model->save()){
            return $this->response()->success('账号成功封禁!')->refresh();
        }
        return $this->response()->error('系统错误,请联系开发人员!');
    }

}