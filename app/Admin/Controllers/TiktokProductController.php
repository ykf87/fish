<?php

namespace App\Admin\Controllers;

use App\Models\TiktokAccount;
use App\Models\TiktokShop;
use App\Models\TiktokProduct;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

use App\Admin\Extensions\BatchComm;

class TiktokProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Tiktok产品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TiktokProduct());
        $admin_id   = Admin::user()->id;
        $grid->model()->where('aid', $admin_id);
        if(request()->get('account_id')){
            $grid->model()->where('account_id', request()->get('account_id'));
        }
        if(request()->get('shop_id')){
            $grid->model()->where('shop_id', request()->get('shop_id'));
        }
        $accounts       = TiktokAccount::where('aid', $admin_id)->pluck('seller_name', 'id')->toArray();
        $shops          = TiktokShop::where('aid', $admin_id)->pluck('shop_region', 'id')->toArray();

        $grid->column('id', __('编号'))->sortable();
        $grid->column('account_id', __('授权账号'))->display(function($val) use($accounts){
            return $accounts[$val] ?? $val;
        })->filter($accounts);
        $grid->column('shop_id', __('店铺地区'))->display(function($val) use($shops){
            return $shops[$val] ?? $val;
        })->filter($shops);
        $grid->column('pid', __('产品id'))->hide();
        $grid->column('name', __('产品名称'))->display(function($val){
            $len        = mb_strlen($val, 'utf-8');
            $str        = $val;
            $maxlen     = 20;
            if($len >= 20){
                $str    = mb_substr($val, 0, ($maxlen-1), 'utf-8') . '...';
            }
            return '<a title="'.$val.'">'.$str.'</a>';
        })->filter();
        $grid->column('create_time', __('上架时间'))->display(function($val){
            return $val ? date('Y-m-d H:i:s', $val) : '';
        })->filter('range', 'datetime');
        $grid->column('status', __('状态'))->using(TiktokProduct::$status)->filter(TiktokProduct::$status)->label(TiktokProduct::$statusLabel);
        $grid->column('currency', __('货币'));
        $grid->column('maxprice', __('最高价'))->filter('range')->sortable();
        $grid->column('minprice', __('最低价'))->filter('range')->sortable();
        $grid->column('commission', __('佣金比例'))->filter('range')->sortable()->editable();
        $grid->column('stocks', __('总库存'))->filter('range');


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
        $grid->tools(function ($tools) {
            $tools->append(new BatchComm());
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    // protected function detail($id)
    // {
    //     $show = new Show(TiktokProduct::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('aid', __('Aid'));
    //     $show->field('account_id', __('Account id'));
    //     $show->field('shop_id', __('Shop id'));
    //     $show->field('pid', __('Pid'));
    //     $show->field('name', __('Name'));
    //     $show->field('create_time', __('Create time'));
    //     $show->field('status', __('Status'));
    //     $show->field('maxprice', __('Maxprice'));
    //     $show->field('minprice', __('Minprice'));
    //     $show->field('commission', __('Commission'));
    //     $show->field('currency', __('Currency'));
    //     $show->field('stocks', __('Stocks'));

    //     return $show;
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TiktokProduct());

        // $form->number('aid', __('Aid'));
        // $form->number('account_id', __('Account id'));
        // $form->number('shop_id', __('Shop id'));
        // $form->text('pid', __('Pid'));
        // $form->text('name', __('Name'));
        // $form->number('create_time', __('Create time'));
        // $form->switch('status', __('Status'));
        // $form->decimal('maxprice', __('Maxprice'));
        // $form->decimal('minprice', __('Minprice'));
        $form->decimal('commission', __('Commission'));
        // $form->text('currency', __('Currency'));
        // $form->number('stocks', __('Stocks'));

        return $form;
    }
}
