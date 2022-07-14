<?php

namespace App\Admin\Actions\Task;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use App\Models\TaskType;
use App\Models\Device;
use App\Models\Account;
use App\Models\Task;
use Illuminate\Support\MessageBag;
use App\Globals\WbApi;
use Encore\Admin\Facades\Admin;

class Send extends RowAction
{
    public $name = '启动';
    public function dialog(){
        $this->confirm('确定启动任务?');
    }

    public function handle(Model $form){
        if($form->task_id){
            $tskType                    = TaskType::find($form->task_id);
            $data   = [
                'configs'   => $form->configs,
                'quality'   => $form->quality,
                'file'      => $tskType->file,
                'id'        => $form->id,
                'title'     => $form->name,
                'req_time'  => time(),
            ];
            $arr    = [
                'type'      => $tskType->type,
                'data'      => $data,
                'code'      => 200,
                'msg'       => '',
                'noreback'  => false,
            ];
            $accountsId         = array_filter($form->account_id);
            if($accountsId){
                $devicesId      = Account::whereIn('id', $accountsId)->pluck('did', 'did')->toArray();
            }else{
                $devicesId      = $form->device_id;
            }

            if(!$tskType){
                return $this->response()->error('请选择任务类型!')->refresh();
                // $error = new MessageBag([
                //     'title'   => '错误',
                //     'message' => '请选择任务类型!',
                // ]);
                // return back()->with(compact('error'));
            }
            // 除了基于设备的任务,其余的都是基于账号的任务
            if($tskType->isdevice != 1){
                if(!$accountsId){
                    return $this->response()->error('当前任务设置没有账号!')->refresh();
                    // $error = new MessageBag([
                    //     'title'   => '错误',
                    //     'message' => '当前任务设置没有账号!',
                    // ]);
                    // return back()->with(compact('error'));
                }
            }
            // 将配置按设备区分,并带上账号信息
            $acarr          = [];
            $accountObj     = Account::whereIn('id', $form->account_id)->get()->toArray();
            if($tskType->model){// 有内置方法
                $rs         = call_user_func_array(['App\Models\TaskType', $tskType->model], [$form, $arr]);
                if($rs !== true){
                	return $this->response()->error($rs)->refresh();
                    // $error = new MessageBag([
                    //     'title'   => '错误',
                    //     'message' => $rs,
                    // ]);
                    // return back()->with(compact('error'));
                }
            }else{
                $task           = Task::find($form->id);
                $task->status   = 0;
                $task->save();
                $rs     = WbApi::send(Admin::user()->id, implode(',', $devicesId), $arr);
                if($rs){
	                $rs     = json_decode($rs, true);
	                if($rs['code'] != 200){
	                	return $this->response()->error($rs['msg'])->refresh();
	                }
	            }else{
	            	return $this->response()->error('服务未启动...')->refresh();
	            }
            }
        }else{
        	return $this->response()->error('任务需要设置任务类型!')->refresh();
        }

        return $this->response()->success('启动成功!')->refresh();
    }

}