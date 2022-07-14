<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Device;

class BatchDeviceDelete extends BatchAction
{
    public $name = '删除设备';

    public function handle(Collection $collection)
    {
    	foreach ($collection as $model) {
    		if($model->status == 1){
    			return $this->response()->error('在线设备无法删除!')->refresh();
    		}
    	}
        foreach ($collection as $model) {
            $res 	= $model->removes();
            if($res !== true){
            	$msg 	= is_string($res) ? $res : '删除失败';
            	return $this->response()->error($msg)->refresh();
            }
        }

        return $this->response()->success('删除成功!')->refresh();
    }

}