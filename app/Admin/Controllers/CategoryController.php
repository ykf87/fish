<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Tree;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '分类管理';

    public function index(Content $content)
    {
        $tree = new Tree(new Category);

        return $content
            ->header($this->title)
            ->body($tree);
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Category());

        $form->select('parent_id', __('请选择上级分类'))->options(Category::selectOptions())->default(1)->required();
        $form->text('title', __('类别名称'))->required();
        $form->text('mark', __('特殊标识'))->placeholder('特殊标识：可以自由定义使用');
        $form->textarea('description', '类别简介');
        $form->image('icon', __('封面图标'))->uniqueName();
        $form->number('order', __('排序'))->default(0);

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());

        $grid->column('id', __('Id'));
        $grid->column('parent_id', __('Parent id'));
        $grid->column('order', __('Order'));
        $grid->column('title', __('Title'));
        $grid->column('icon', __('Icon'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Category::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('parent_id', __('Parent id'));
        $show->field('order', __('Order'));
        $show->field('title', __('Title'));
        $show->field('icon', __('Icon'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }


}
