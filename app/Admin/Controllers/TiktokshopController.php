<?php

namespace App\Admin\Controllers;

use App\Models\TiktokAccount;
use App\Models\TiktokShop;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use App\Tiktok\Shop;
use App\Admin\Actions\Tiktok\Getproduct;
use App\Admin\Actions\Tiktok\Getorder;

class TiktokshopController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '店铺管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(){
        $loginId        = Admin::user()->id;
        $tkaccounts     = TiktokAccount::where('aid', $loginId)->pluck('seller_name', 'id')->toArray();

        $grid       = new Grid(new TiktokShop());
        $grid->model()->where('aid', $loginId);
        $whereId    = request()->get('account_id');
        if($whereId){
            $grid->model()->where('account_id', $whereId);
        }

        $grid->column('id', __('编号'));
        $grid->column('account_id', __('所属账号'))->display(function($val) use($tkaccounts){
            return $tkaccounts[$val] ?? $val;
        });
        $grid->column('shop_id', __('店铺ID'));
        $grid->column('shop_region', __('所在地区'))->display(function($val){
            return Shop::$resion[$val] ?? $val;
        });
        $grid->column('product_number', __('已拉取产品数量'));
        $grid->column('sales', __('订单总数'));


        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();

            $actions->add(new Getproduct);
            $actions->add(new Getorder);
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
        $show = new Show(TiktokShop::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('aid', __('Aid'));
        $show->field('account_id', __('Account id'));
        $show->field('shop_id', __('Shop id'));
        $show->field('shop_region', __('Shop region'));
        $show->field('product_number', __('Product number'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TiktokShop());

        $form->number('aid', __('Aid'));
        $form->number('account_id', __('Account id'));
        $form->text('shop_id', __('Shop id'));
        $form->text('shop_region', __('Shop region'));
        $form->number('product_number', __('Product number'));

        return $form;
    }
}
