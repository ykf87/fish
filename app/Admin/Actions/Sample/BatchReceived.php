<?php

namespace App\Admin\Actions\Sample;

use App\Models\TiktokSample;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchReceived extends BatchAction
{
    public $name = '批量 - 已收货';

    public function handle(Collection $collection)
    {
        $ids = $collection->pluck('id')->toArray();
        $num = TiktokSample::whereIn('id', $ids)->where('status', 2)->update(['status' => 3]);

        return $this->response()->success(sprintf("批量设置-已收货 %s 个申请", $num))->refresh();

    }

}