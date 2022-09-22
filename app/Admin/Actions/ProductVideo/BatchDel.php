<?php

namespace App\Admin\Actions\ProductVideo;

use App\Models\TiktokProduct;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;

class BatchDel extends BatchAction
{
    public $name = '批量删除视频';

    public function handle(Collection $collection)
    {
        $count = 0;
        foreach ($collection as $item) {
            if ($item->aid == Admin::user()->id || Admin::user()->isRole('administrator')) {
                $item->delete();
                TiktokProduct::query()->where('id', $item->pid)->decrement($item->type . '_video_num');
                $count++;
            }
        }

        return $this->response()->success(sprintf("成功删除 %s 个视频", $count))->refresh();
    }

}
