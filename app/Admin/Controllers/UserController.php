<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->orderByDesc('id');
        $state  = [
            0   => '否',
            1   => '是',
        ];

        $grid->column('id', __('ID'));
        $grid->column('nickname', __('昵称'));
        $grid->column('email', __('邮箱'));
        $grid->column('avatar', __('头像'))->image('', 40, 40);
        $grid->column('phone', __('电话'));
        // $grid->column('password', __('Password'));
        // $grid->column('singleid', __('Singleid'));
        // $grid->column('integral', __('Integral'));
        $grid->column('invitation_code', __('邀请码'));
        // $grid->column('parent_invite', __('Parent invite'));
        $grid->column('pid', __('邀请人'));
        // $grid->column('relation', __('关系链'));
        $grid->column('status', __('状态'));
        // $grid->column('original_video_num', __('已领取原始视频数量'));
        // $grid->column('clip_video_num', __('已领取剪辑视频数量'));
        $grid->column('register_ip', __('注册ip'));
        // $grid->column('last_ip', __('Last ip'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        $grid->column('inviteurl', __('邀请链接'));
        $grid->column('agent', __('是否代理商'))->switch($state);

        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();
        $grid->disableColumnSelector();
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
        $show = new Show(User::findOrFail($id));

        // $show->field('id', __('Id'));
        // $show->field('nickname', __('Nickname'));
        // $show->field('email', __('Email'));
        // $show->field('avatar', __('Avatar'));
        // $show->field('phone', __('Phone'));
        // $show->field('password', __('Password'));
        // $show->field('singleid', __('Singleid'));
        // $show->field('integral', __('Integral'));
        // $show->field('invitation_code', __('Invitation code'));
        // $show->field('parent_invite', __('Parent invite'));
        // $show->field('pid', __('Pid'));
        // $show->field('relation', __('Relation'));
        // $show->field('status', __('Status'));
        // $show->field('original_video_num', __('Original video num'));
        // $show->field('clip_video_num', __('Clip video num'));
        // $show->field('register_ip', __('Register ip'));
        // $show->field('last_ip', __('Last ip'));
        // $show->field('created_at', __('Created at'));
        // $show->field('updated_at', __('Updated at'));
        // $show->field('inviteurl', __('Inviteurl'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('nickname', __('昵称'));
        $form->email('email', __('Email'));
        $form->image('avatar', __('Avatar'));
        $form->mobile('phone', __('Phone'));
        $form->password('password', __('Password'));
        $form->number('singleid', __('Singleid'))->default(1);
        $form->number('integral', __('Integral'));
        $form->text('invitation_code', __('Invitation code'));
        $form->text('parent_invite', __('Parent invite'));
        $form->number('pid', __('Pid'));
        $form->textarea('relation', __('Relation'));
        $form->number('status', __('Status'))->default(1);
        $form->number('original_video_num', __('Original video num'));
        $form->number('clip_video_num', __('Clip video num'));
        $form->text('register_ip', __('Register ip'));
        $form->text('last_ip', __('Last ip'));
        $form->text('inviteurl', __('Inviteurl'));
        $form->switch('agent', __('是否代理'));

        return $form;
    }
}
