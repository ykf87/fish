<?php

namespace App\Admin\Controllers;

use App\Models\TiktokShop;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

use App\Admin\Extensions\Addtiktokshop;

class TiktokshopController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'TikTok店铺管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TiktokShop());
        $grid->model()->where('aid', Admin::user()->id);

        $grid->column('id', __('编号'));
        $grid->column('region', __('店铺地区'));
        $grid->column('shop_id', __('商店id'));
        $grid->column('shop_name', __('店铺名称'));
        $grid->column('type', __('类型'));
        $grid->column('status', __('状态'));
        $grid->column('created_at', __('创建日期'));

        $grid->disableCreateButton();
        $grid->disableExport();


        $grid->tools(function ($tools) {
            $tools->append(new AddTiktokShop());
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
        // $show = new Show(TiktokShop::findOrFail($id));

        // $show->field('id', __('Id'));
        // $show->field('aid', __('Aid'));
        // $show->field('region', __('Region'));
        // $show->field('shop_id', __('Shop id'));
        // $show->field('shop_name', __('Shop name'));
        // $show->field('type', __('Type'));
        // $show->field('status', __('Status'));
        // $show->field('created_at', __('Created at'));
        // $show->field('updated_at', __('Updated at'));

        // return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        // $form = new Form(new TiktokShop());

        // $form->number('aid', __('Aid'));
        // $form->text('region', __('Region'));
        // $form->text('shop_id', __('Shop id'));
        // $form->text('shop_name', __('Shop name'));
        // $form->text('type', __('Type'));
        // $form->switch('status', __('Status'))->default(1);

        // return $form;
    }
}
