<?php

namespace App\Admin\Actions\Account;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class Task extends RowAction
{
    public $name = '发布任务';


    public function handle(Model $model, Request $request){
    	// return $this->response()->success('Success message...')->refresh();
    }
 //    public function form(){
	//     // 文本输入框
	//     $this->image('name', __('上传视频'));
	// }
	public function href(){
		return url('admin/tasks/create?account_id=' . $this->row->id . '&task_id=3');
	}
}