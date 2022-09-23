<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ProductVideo\BatchDel;
use App\Admin\Extensions\CreateButton;
use App\Models\TiktokProduct;
use App\Models\TikTokProductsVideo;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;

class TiktokProductsVideoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品视频管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TiktokProductsVideo());

        $pid = Request::input('pid');

        if ($pid) {
            $grid->model()->where('pid', $pid);
        }

        if (!Admin::user()->isRole('administrator')) {
            $grid->model()->where('aid', Admin::user()->id);
        }

        $grid->column('id', __('编号'));
        $grid->column('product.name', __('商品名称'))->limit('30');
        $grid->column('type', __('视频类型'))->using(TiktokProductsVideo::$type)->label(TiktokProductsVideo::$typeLabel);
        $grid->column('title', __('视频标题'));
        $grid->column('video_url', __('视频 url'));
        $grid->column('receive_status', __('是否领取'))->bool();
        $grid->column('created_at', __('发布时间'));

        $grid->disableExport();
        $grid->disableCreateButton();

        $grid->batchActions(function ($batch) {
            $batch->add(new BatchDel());

            $batch->disableDelete();
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->tools(function ($tools) {
            $tools->append(new CreateButton());
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
        $show = new Show(TikTokProductsVideo::findOrFail($id));

        $show->field('id', __('编号'));
        $show->field('pid', __('商品id'));
        $show->field('type', __('视频类型'));
        $show->field('title', __('视频标题'));
        $show->field('video_url', __('视频'));
        $show->field('receive_status', __('是否领取'));
        $show->field('created_at', __('发布时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TikTokProductsVideo());

        $form->text('aid', __('管理员id'))->default(Admin::user()->id)->disable();
        $form->text('pid', __('商品id'))->default(Request::input('pid'))->required();
        $form->radio('type', __('视频类型'))->options(TiktokProductsVideo::$type)->default('original');
        $form->text('title', __('视频标题'));
        $form->file('video_url', __('上传视频'))->rules('mimes:mp4,png');

        $form->submitted(function (Form $form) {
            if (!$form->model()->pid) {
                TiktokProduct::where('id', Request::input('pid'))->increment(Request::input('type') . '_video_num');
                $form->aid = Admin::user()->id;
            }
        });

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
