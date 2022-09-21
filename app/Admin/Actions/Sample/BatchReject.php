<?php

namespace App\Admin\Actions\Sample;

use App\Models\TiktokSample;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class BatchReject extends BatchAction
{
    public $name = '批量 - 拒绝申请';

    public function handle(Collection $collection, Request $request)
    {
        $rejection =  $request->input('rejection');
        $ids = $collection->pluck('id')->toArray();
        $num = TiktokSample::whereIn('id', $ids)->where('status', 0)->update(['status' => -1, 'rejection' => $rejection]);

        return $this->response()->success(sprintf("拒绝申请 %s 条", $num))->refresh();

    }

    public function form()
    {
        $this->textarea('rejection', '拒绝理由')->rules('required');
    }


}