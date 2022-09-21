<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Sample\BatchApprove;
use App\Admin\Actions\Sample\BatchReceived;
use App\Admin\Actions\Sample\BatchReject;
use App\Admin\Actions\Sample\Delivery;
use App\Models\TiktokSample;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TiktokSampleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '领样申请';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TiktokSample());

        if (!Admin::user()->isRole('administrator')) {
            $grid->model()->where('aid', Admin::user()->id);
        }

        $grid->column('id', __('编号'))->sortable();
        $grid->column('account_id', __('账号id'))->hide();
        $grid->column('darren_id', __('达人id'))->hide();
        $grid->column('darren.nickname', __('达人昵称'));
        $grid->column('pid', __('商品id'))->hide();
        //$grid->column('product_id', __('tiktok商品id'));
        $grid->column('product_name', __('商品名称'))->limit(40);
        $grid->column('product_image', __('商品图片'))->image('', 60, 60);
        $grid->column('addtime', __('申请时间'));
        $grid->column('num', __('申请数量'));
        $grid->column('status', __('领样状态'))
            ->using(TiktokSample::$status)
            ->filter(TiktokSample::$status)
            ->label(TiktokSample::$statusLabel);
        $grid->column('rejection', __('拒绝理由'))->limit(20)->hide();
        $grid->column('remark', __('领样备注'))->limit(30);
        $grid->column('shippment', __('快递公司'))->hide();
        $grid->column('shipnum', __('快递单号'))->hide();
        $grid->column('needtime', __('最迟需要的时间'))->hide();
        //$grid->column('admin_id', __('后台操作人员'))->hide();



        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->batchActions(function ($batch) {
            $batch->add(new BatchApprove());
            $batch->add(new BatchReceived());
            $batch->add(new BatchReject());
            $batch->disableDelete();
        });

        $grid->actions(function ($actions) {
            if ($actions->row['status'] == 1) {
                $actions->add(new Delivery());
            }
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(TiktokSample::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('account_id', __('Account id'));
        $show->field('darren_id', __('Darren id'));
        $show->field('pid', __('Pid'));
        $show->field('product_id', __('Product id'));
        $show->field('product_name', __('Product name'));
        $show->field('product_image', __('Product image'));
        $show->field('addtime', __('Addtime'));
        $show->field('admin_id', __('Admin id'));
        $show->field('num', __('Num'));
        $show->field('remark', __('Remark'));
        $show->field('needtime', __('Needtime'));
        $show->field('status', __('Status'));
        $show->field('rejection', __('Rejection'));
        $show->field('shippment', __('Shippment'));
        $show->field('shipnum', __('Shipnum'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TiktokSample());

//        $form->number('account_id', __('Account id'));
//        $form->number('darren_id', __('Darren id'));
        $form->number('pid', __('Pid'));
        $form->text('product_id', __('商品id'));
        $form->text('product_name', __('商品名称'));
        $form->textarea('product_image', __('商品图片'));
        $form->datetime('addtime', __('申请时间'));
        $form->number('admin_id', __('后台操作人员'));
        $form->number('num', __('申请数量'))->default(1);
        $form->textarea('remark', __('领样备注'));
        $form->number('needtime', __('最迟需要的时间'));
        $form->switch('status', __('领样状态'));
        $form->textarea('rejection', __('拒绝理由'));
        $form->text('shippment', __('快递公司'));
        $form->text('shipnum', __('快递单号'));

        return $form;
    }
}
