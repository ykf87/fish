<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;

class BannerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '选品页轮播图';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner());

        $grid->model()->orderByDesc('id');
        $grid->column('id', __('编号'))->sortable();
        $grid->column('name', __('名称'))->filter('like');
        $grid->column('image', __('图片'))->display(function ($val) {
            return $val ? '<img src=' . Storage::disk('s3')->url($val) . ' style="max-width:50px;max-height:50px;" />' : '';
        });
        $grid->column('url', __('图片跳转链接'));

        $grid->disableExport();
        $grid->actions(function ($actions) {
            // 去掉编辑
            // $actions->disableEdit();
            // 去掉查看
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
        $show = new Show(Banner::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Banner());
        $form->text('name', __('图片名称'))->default('选品页轮播图');
        // 修改图片上传路径和文件名
        $form->image('image', __('图片'))->move('banner')->uniqueName();
        $form->text('url', __('图片跳转链接'));
        return $form;
    }
}
