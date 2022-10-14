<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\DropdownMenu;
use App\Models\Dict;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

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
        $grid->column('dict_val', __('字典val'))->display(function () {
            if ($this->type == 'json') {
                $val = json_encode($this->dict_val_json);
            } else {
                $val = $this->dict_val;
            }
            return strip_tags(Str::limit($val, 80));
        });
        $grid->column('tag', __('标签'));

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();

        $grid->tools(function ($tools) {
            $base_url = admin_url('dicts') . '/create?type=';
            $data = [
                'button_name' => '添加字典',
                'list' => [
                    'text - 类型' => $base_url . 'text',
                    'divider1' => 'divider',
                    'json - 类型' => $base_url . 'json',
                    'divider2' => 'divider',
                    'html - 富文本类型' => $base_url . 'html',
                ]
            ];

            $tools->append(new DropdownMenu($data));
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

        $form->text('name', __('字典名称'))->required();
        $form->text('dict_key', __('字典key'))->required();

        if ($form->isEditing()) {
            $id = request()->route()->parameters()['dict'];
            $type = Dict::find($id)['type'];
        } else {
            $type = Request::input('type');
            $form->hidden('type')->value($type);
        }

        switch ($type) {
            case 'text':
                $form->textarea('dict_val', __('字典val'));
                break;
            case 'json':
                $form->keyValue('dict_val_json', __('字典val'))->rules('required|min:0');
                break;
            case 'html':
                $form->ckeditor('dict_val', __('字典val'));
                break;
            default :
                $form->textarea('dict_val', __('字典val'));
        }

        $form->tags('tag', __('标签'));

        return $form;
    }
}
