<?php

namespace App\Admin\Controllers;

use App\Models\Dict;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DictController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '字典管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Dict());

        $grid->column('id', __('编号'));
        $grid->column('type', __('类型'));
        $grid->column('name', __('字典名称'));
        $grid->column('dict_key', __('字典key'));
        $grid->column('dict_val', __('字典val'));
        $grid->column('tag', __('标签'));

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
        $show = new Show(Dict::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('name', __('Name'));
        $show->field('dict_key', __('Dict key'));
        $show->field('dict_val', __('Dict val'));
        $show->field('tag', __('Tag'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Dict());

        $form->text('type', __('类型'));
        $form->text('name', __('字典名称'));
        $form->text('dict_key', __('字典key'));
        $form->textarea('dict_val', __('字典val'));
        $form->text('tag', __('标签'));

        return $form;
    }
}
