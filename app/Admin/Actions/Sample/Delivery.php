<?php

namespace App\Admin\Actions\Sample;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Delivery extends RowAction
{
    public $name = '快递发货';

    public function handle(Model $model, Request $request)
    {
        if ($model->status !== 1) {
            return $this->response()->error('只有 审核通过 的订单才能发货');
        }
        $model->shippment = $request->input('shippment');
        $model->shipnum = $request->input('shipnum');
        $model->status = 2;
        $model->save();

        return $this->response()->success('发货成功')->refresh();
    }

    public function form(Model $model)
    {
        if ($model->status !== 1) {
            return $this->response()->error('只有 审核通过 的订单才能发货')->refresh();
        }

        $this->text('product_name', '商品名称')->disable()->placeholder($model->product_name);
        $this->text('darren', '申请达人')->disable()->placeholder($model->darren->nickname);
        $this->text('shippment', '快递公司')->required();
        $this->text('shipnum', '快递单号')->required();
        $this->display('product', $model->product_name);

    }

}