<?php

namespace App\Admin\Actions\Sample;

use App\Models\TiktokSample;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchApprove extends BatchAction
{
    public $name = '批量 - 审核通过';

    public function handle(Collection $collection)
    {
        $ids = $collection->pluck('id')->toArray();
        $num = TiktokSample::whereIn('id', $ids)->where('status', 0)->update(['status' => 1]);

        return $this->response()->success(sprintf("成功审核通过 %s 个申请", $num))->refresh();

    }

}