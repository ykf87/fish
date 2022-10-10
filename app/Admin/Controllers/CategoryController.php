<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Route;

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
        Admin::style('
        .dd-handle{margin: 8px 0;}
        .pull-right .fa{font-size: 16px; margin-left: 20px; padding-top: 3px;}
        ');
        return $content
            ->header($this->title)
            ->description('列表')->row(function (Row $row) {
                // 左侧显示树形分类
                $tree = new Tree(new Category());
                $tree->disableCreate();// 禁用新增按钮
                // 修改返回结构
                $tree->branch(function ($branch) {
                    if ($branch['parent_id'] == 0) {
                        $str = "<b style='font-size:16px;'>{$branch['id']} - {$branch['title']}</b>";
                    } else {
                        $str = "{$branch['id']} - {$branch['title']}";
                    }
                    return $str;
                });
                $row->column(6, $tree);
                //  右侧显示新增框
                $row->column(6, function (Column $column) {
                    $column->append( $this->form() );
                });
            });

    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Category());

        $form->select('parent_id', __('上级分类'))->options(Category::selectOptions())->default(1)->required();
        $form->text('title', __('类别名称'))->required();
        $form->text('mark', __('特殊标识'))->placeholder('特殊标识：可以自由定义使用');
        $form->textarea('description', '类别简介');
        $form->image('icon', __('封面图标'))->uniqueName();
        $form->number('order', __('排序'))->default(0);

        $form->header(function ($header) {
            $header->disableList();
        });

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        if (strstr(Route::currentRouteName(), '.index')) {
            $form->setAction(admin_url('categories'));
        }

        $form->saved(function () {
            return redirect(admin_url('categories'));
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
