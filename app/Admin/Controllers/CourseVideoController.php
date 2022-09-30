<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\CreateButton;
use App\Models\CourseVideo;
use App\services\CourseService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request as FRequest;
use Illuminate\Support\MessageBag;

class CourseVideoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '课程 - 视频';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CourseVideo());

        $course_id = FRequest::input('course_id');

        if ($course_id) {
            $grid->model()->where('course_id', $course_id);
        }

        if (!Admin::user()->isRole('administrator')) {
            $grid->model()->where('aid', Admin::user()->id);
        }

        $grid->column('id', __('Id'));
        $grid->column('course.title', __('所属课程'));
        $grid->column('order', __('排序'));
        $grid->column('title', __('视频标题'));
        $grid->column('charge_type', __('收费类型'))->filter(CourseVideo::$chargeType)->using(CourseVideo::$chargeType)->label(CourseVideo::$chargeTypeLabel);
        $status = [
            'on'  => ['value' => 1, 'text' => '已上架', 'color' => 'primary'],
            'off' => ['value' => -1, 'text' => '已下架', 'color' => 'default'],
        ];
        $grid->column('status', __('上架状态'))->filter(CourseVideo::$status)->switch($status);
        $grid->column('pic', __('封面图'))->image('', 60, 60);
        $grid->column('video_url', __('视频url'));
        $grid->column('views', __('观看次数'));
        $grid->column('created_at', __('创建时间'));

        $grid->disableExport();
        $grid->disableCreateButton();

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->tools(function ($tools) {
            if (!empty(FRequest::input('course_id'))) {
                $data = [
                    'button_name' => '新增 - 课程视频',
                    'url' => admin_url(sprintf('course-videos/create?course_id=%s', FRequest::input('course_id'))),
                ];
                $tools->append(new CreateButton($data));
            }

            $data = [
                'button_name' => '返回 - 课程列表',
                'url' => admin_url('courses'),
                'btn_class' => 'btn btn-sm btn-default',
                'fa_icon' => 'none'
            ];
            $tools->append(new CreateButton($data));
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
        $show = new Show(CourseVideo::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('course_id', __('Course id'));
        $show->field('order', __('Order'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('charge_type', __('Charge type'));
        $show->field('pic', __('Pic'));
        $show->field('video_url', __('Video url'));
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
        $form = new Form(new CourseVideo());

        if ($form->isEditing()) {
            $form->text('course.title', __('所属课程'))->disable();
        } else {
            $course_id = FRequest::input('course_id');
            if (empty($course_id)) {
                $error = new MessageBag([
                    'title'   => '出错啦',
                    'message' => 'course_id不能为空....',
                ]);
                return back()->with(compact('error'));
            }
            $courseService = new CourseService();
            $course_title = $courseService->info($course_id, ['title'])['title'];
            $form->text('course_title', __('所属课程'))->disable()->value($course_title);
            $form->hidden('course_id')->value($course_id);
        }

        $form->text('title', __('视频标题'))->required();
        $form->textarea('description', __('视频简介'));
        $form->radio('charge_type', __('收费类型'))->options(CourseVideo::$chargeType)->required();
        $form->image('pic', __('封面图'))->uniqueName();
        $form->number('order', __('排序'))->default(0);
        $form->text('video_url', __('视频url'))->required();
        $form->file_upload('video_upload', __('上传视频'));
        $form->radio('status', __('上架状态'))->options(CourseVideo::$status)->default(1);

        $form->saved(function (Form $form) {
            $courseService = new CourseService();
            $courseService->countVideo($form->model()->course_id);
        });

        return $form;
    }
}
