<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class UnTitle extends RowAction{
    public $name = '解封';

    public function dialog(){
        $this->confirm('确定解封?');
    }
    public function handle(Model $model){
        if($model->status >= 0){
            return $this->response()->error('该账号未被封禁,无需解封!');
        }
        $model->status      = 1;
        $model->singleid    = 0;
        if($model->save()){
            return $this->response()->success('账号成功解封!')->refresh();
        }
        return $this->response()->error('系统错误,请联系开发人员!');
    }

}