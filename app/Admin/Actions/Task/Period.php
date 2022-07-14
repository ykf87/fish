<?php

namespace App\Admin\Actions\Task;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
class Period extends RowAction
{
    public $name = '周期设置';
    public function href()
    {
    	// $getRow = $this->getRow()->toArray();
    	return "/admin/tasks/cycle?t_id=".$this->getKey();
    }
    // public function handle(Model $model, Request $request){
        // return $this->response()->success('周期性设置还在开发中')->refresh();
    // }
 //    public function form(Model $model){
    	
	// 	return view('task.add')
 //        ->with('title','周期设置')->render();
	//     $this->date('starttime', '开始时间')->value($model->starttime);
	//     $this->date('endtime', '结束时间')->value($model->endtime);
	// }
}