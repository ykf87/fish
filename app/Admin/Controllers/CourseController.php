<?php

namespace App\Admin\Controllers;

use App\Models\Course;
use App\services\CategoryService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CourseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '课程管理';
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Course());

        $grid->column('id', __('Id'));
        $grid->column('category.title', __('所属分类'));
        $grid->column('order', __('排序'));
        $grid->column('title', __('课程名称'));
        $grid->column('charge_type', __('收费类型'))->using(Course::$chargeType)->filter(Course::$chargeType)->label(Course::$chargeTypeLabel);
        $status = [
            'on'  => ['value' => 1, 'text' => '已上架', 'color' => 'primary'],
            'off' => ['value' => -1, 'text' => '已下架', 'color' => 'default'],
        ];
        $grid->column('status', __('上架状态'))->filter(Course::$status)->switch($status);
        $grid->column('original_price', __('原价'));
        $grid->column('price', __('现价'));
        $grid->column('pic', __('封面图'));
        $grid->column('video_num', '视频数量')->display(function () {
            $url = admin_url('course-videos?course_id=' . $this->id);
            return sprintf("<a href='%s'>%s</a>", $url, $this->video_num);
        })->sortable();
        $grid->column('views', __('观看次数'));
        $grid->column('created_at', __('创建时间'));

        $grid->disableExport();

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
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
        $show = new Show(Course::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('category_id', __('Cagegory id'));
        $show->field('order', __('Order'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('charge_type', __('Charge type'));
        $show->field('original_price', __('Original price'));
        $show->field('price', __('Price'));
        $show->field('pic', __('Pic'));
        $show->field('video_num', __('Video num'));
        $show->field('views', __('Views'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Course());

        $cate_options = $this->categoryService->getListByMark('video_course', 'pluck');

        $form->select('category_id', __('所属分类'))->options($cate_options)->required();
        $form->text('title', __('课程名称'))->required();
        $form->textarea('description', __('课程简介'));
        $form->decimal('original_price', __('原价'))->default(0.00);
        $form->decimal('price', __('现价'))->default(0.00);
        $form->image('pic', __('封面图'));
        $form->number('order', __('排序'))->default(0);
        $form->radio('status', __('上架状态'))->options(Course::$status)->default(1);

        $form->saving(function (Form $form) {
            if ($form->isCreating()) {
                $form->model()->aid = Admin::user()->id;
            }
        });

        return $form;
    }
}
