<?php

namespace App\Admin\Controllers;

use App\Models\TiktokOrder;
use App\Models\TiktokAccount;
use App\Models\TiktokShop;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Extensions\BatchGetOrder;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;

class TiktokOrdersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Tiktok订单管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TiktokOrder());
        $admin_id   = Admin::user()->id;
        $grid->model()->where('aid', $admin_id);
        if(request()->get('account_id')){
            $grid->model()->where('account_id', request()->get('account_id'));
        }
        if(request()->get('shop_id')){
            $grid->model()->where('shop_id', request()->get('shop_id'));
        }
        $grid->model()->orderByDesc('addtime');
        $accounts       = TiktokAccount::where('aid', $admin_id)->pluck('seller_name', 'id')->toArray();
        $shops          = TiktokShop::where('aid', $admin_id)->pluck('shop_region', 'id')->toArray();

        $grid->column('id', __('编号'));
        $grid->column('account_id', __('授权账号'))->display(function($val) use($accounts){
            return $accounts[$val] ?? $val;
        })->filter($accounts);
        $grid->column('shopid', __('店铺'))->display(function($val) use($shops){
            return $shops[$val] ?? $val;
        })->filter($shops);
        $grid->column('order_id', __('订单号'))->filter('like');
        $grid->column('payment', __('付款方式'))->hide();
        $grid->column('paytime', __('支付时间'))->hide();
        $grid->column('shipment', __('配送方式'))->hide();
        $grid->column('addtime', __('下单时间'))->display(function($val){
            return $val ? date('Y-m-d H:i:s', $val) : null;
        })->filter('range');
        $grid->column('pro_num', __('产品数量'))->modal('订单产品详情', function ($model) {
            $comments = $model->pros()->take(10)->get()->map(function ($comment) {
                return $comment->only(['sku_name', 'quantity', 'sku_sale_price']);
            });

            return new Table(['属性名称', '数量', '单价'], $comments->toArray());
        })->sortable();
        $grid->column('remark', __('买家备注'))->hide();
        $grid->column('currency', __('货币'));
        $grid->column('sub_total', __('产品总额'))->filter('range');
        $grid->column('shipping_fee', __('快递费'))->hide();
        $grid->column('total_amount', __('付款金额'))->filter('range');
        $grid->column('resion', __('收货地区'));
        $grid->column('fulladdress', __('收货地址'))->hide()->filter('like');
        $grid->column('name', __('收货人'))->hide()->filter('like');
        $grid->column('phone', __('联系电话'))->display(function($val){
            return $val;
        })->filter('like');
        $grid->column('status', __('订单状态'))->using(TiktokOrder::$status)->filter(TiktokOrder::$status);//->label(TiktokOrder::$statusLabel);
        // $grid->column('buyer_uid', __('Buyer uid'));



        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
        });
        // $grid->tools(function ($tools) {
        //     $tools->append(new BatchGetOrder());
        // });

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
        $show = new Show(TiktokOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('aid', __('Aid'));
        $show->field('account_id', __('Account id'));
        $show->field('shopid', __('Shopid'));
        $show->field('order_id', __('Order id'));
        $show->field('status', __('Status'));
        $show->field('payment', __('Payment'));
        $show->field('shipment', __('Shipment'));
        $show->field('created', __('Created'));
        $show->field('paytime', __('Paytime'));
        $show->field('remark', __('Remark'));
        $show->field('currency', __('Currency'));
        $show->field('sub_total', __('Sub total'));
        $show->field('shipping_fee', __('Shipping fee'));
        $show->field('total_amount', __('Total amount'));
        $show->field('resion', __('Resion'));
        $show->field('region_code', __('Region code'));
        $show->field('state', __('State'));
        $show->field('city', __('City'));
        $show->field('fulladdress', __('Fulladdress'));
        $show->field('name', __('Name'));
        $show->field('phone', __('Phone'));
        $show->field('buyer_uid', __('Buyer uid'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TiktokOrder());

        $form->number('aid', __('Aid'));
        $form->number('account_id', __('Account id'));
        $form->number('shopid', __('Shopid'));
        $form->text('order_id', __('Order id'));
        $form->number('status', __('Status'));
        $form->text('payment', __('Payment'));
        $form->text('shipment', __('Shipment'));
        $form->number('created', __('Created'));
        $form->number('paytime', __('Paytime'));
        $form->textarea('remark', __('Remark'));
        $form->text('currency', __('Currency'));
        $form->decimal('sub_total', __('Sub total'));
        $form->decimal('shipping_fee', __('Shipping fee'));
        $form->decimal('total_amount', __('Total amount'));
        $form->text('resion', __('Resion'));
        $form->number('region_code', __('Region code'));
        $form->text('state', __('State'));
        $form->text('city', __('City'));
        $form->textarea('fulladdress', __('Fulladdress'));
        $form->text('name', __('Name'));
        $form->mobile('phone', __('Phone'));
        $form->text('buyer_uid', __('Buyer uid'));

        return $form;
    }
}
