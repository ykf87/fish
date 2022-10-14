<?php

namespace App\Admin\Controllers;

use App\Models\CourseOrder;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CourseOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '课程订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CourseOrder());

        $grid->column('id', __('编号'));
        $grid->column('user.nickname', __('用户'));
        $grid->column('course.title', __('所属课程'));
        $grid->column('product', __('订单标题'));
        $grid->column('description', __('用户备注'));
        $grid->column('payment_type', __('付款方式'));
        $grid->column('currency', __('支付货币'));
        $grid->column('price', __('订单价格'))->hide();
        $grid->column('shipping', __('Shipping'))->hide();
        $grid->column('total', __('买家付款金额'));
        $grid->column('pay_id', __('paypal订单号'));
        $grid->column('payer_info', __('支付人'))->hide();
        $grid->column('status', __('支付状态'))->filter(CourseOrder::$status)->using(CourseOrder::$status)->label(CourseOrder::$statusLabel);
        $grid->column('addtime', __('创建时间'));
        $grid->column('paytime', __('付款时间'));

        $grid->disableExport();
        $grid->disableCreateButton();

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
            $actions->disableEdit();
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
        $show = new Show(CourseOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_no', __('Order no'));
        $show->field('uid', __('Uid'));
        $show->field('course_id', __('Course id'));
        $show->field('product', __('Product'));
        $show->field('description', __('Description'));
        $show->field('payment_type', __('Payment type'));
        $show->field('currency', __('Currency'));
        $show->field('price', __('Price'));
        $show->field('shipping', __('Shipping'));
        $show->field('total', __('Total'));
        $show->field('pay_id', __('Pay id'));
        $show->field('payer_info', __('Payer info'));
        $show->field('status', __('Status'));
        $show->field('addtime', __('Addtime'));
        $show->field('paytime', __('Paytime'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CourseOrder());

        $form->text('order_no', __('Order no'));
        $form->number('uid', __('Uid'));
        $form->text('course_id', __('Course id'));
        $form->text('product', __('Product'));
        $form->textarea('description', __('Description'));
        $form->text('payment_type', __('Payment type'));
        $form->text('currency', __('Currency'));
        $form->decimal('price', __('Price'));
        $form->decimal('shipping', __('Shipping'))->default(0.00);
        $form->decimal('total', __('Total'));
        $form->text('pay_id', __('Pay id'));
        $form->text('payer_info', __('Payer info'));
        $form->switch('status', __('Status'))->default(1);
        $form->number('addtime', __('Addtime'));
        $form->number('paytime', __('Paytime'));

        return $form;
    }
}
