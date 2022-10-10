<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ProductVideo\BatchDel;
use App\Admin\Extensions\CreateButton;
use App\Models\TiktokProduct;
use App\Models\TiktokProductsVideo;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FRequest;
use Illuminate\Support\Facades\Storage;


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

        $pid = FRequest::input('pid');
        $type = FRequest::input('type');

        if ($pid) {
            $grid->model()->where('pid', $pid);
        }

        if ($type) {
            $grid->model()->where('type', $type);
        }

        if (!Admin::user()->isRole('administrator')) {
            $grid->model()->where('aid', Admin::user()->id);
        }

        $grid->column('id', __('编号'));
        $grid->column('product.name', __('商品名称'))->limit('30');
        $grid->column('type', __('视频类型'))->using(TiktokProductsVideo::$type)->label(TiktokProductsVideo::$typeLabel);
        $grid->column('title', __('视频标题'));
        $grid->column('full_video_url', __('视频 url'));
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
            $data = [
                'button_name' => '新增视频',
                'url' => admin_url(sprintf('tiktok-products-videos/create?pid=%s&type=%s', FRequest::input('pid'), FRequest::input('type'))),
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

        $form->text('pid', __('商品id'))->default(FRequest::input('pid'))->required();
        $form->radio('type', __('视频类型'))->options(TiktokProductsVideo::$type)->default(FRequest::input('type'))->required();
        $form->text('title', __('视频标题'));
        $form->text('video_url', __('视频url'))->required();
        $form->file_upload('video_upload', __('上传视频'));

        $form->saving(function (Form $form) {
            if ($form->isCreating()) {
                TiktokProduct::where('id', FRequest::input('pid'))->increment(FRequest::input('type') . '_video_num');
                $form->model()->aid = Admin::user()->id;
            }
        });

        return $form;
    }

    public function fileUpload(Request $request){
        $save_dir_abspath = 'upload_big';
        $temp_save_dir = 'upload_big_temp/' . Admin::user()->id;
        if(!Storage::exists($temp_save_dir)){  //临时文件夹
            Storage::makeDirectory($temp_save_dir);
        }

        $block=$request->file('file');
        $block_id=$request->input('id');  //0~tot-1
        $block_tot=$request->input('total');

        if (isset($block_id)) {
            $block->move(storage_path('app/'.$temp_save_dir),$block_id); //以块号为名保存当前块
        }

        if($block_tot - 1 == $block_id){  //整个文件上传完成
            $file_info = pathinfo($request->input('name'));
            $save_name = time() . rand(10000, 99999999) . '.' . $file_info['extension'];
            $local_file = $save_dir_abspath . '/' . $save_name;
            if (!is_dir($save_dir_abspath))
                mkdir($save_dir_abspath, 0777, true);  // 文件夹不存在则创建
            for($i=0; $i < $block_tot; $i++){
                $content=Storage::get($temp_save_dir . '/' . $i);
                file_put_contents($local_file, $content, $i ? FILE_APPEND:FILE_TEXT);//追加:覆盖
            }
            $s3_file = 'file/' . $save_name;
            Storage::disk('s3')->put($s3_file, file_get_contents($local_file));
            Storage::deleteDirectory($temp_save_dir); //删除临时文件
            unlink($local_file);
            return ['success' => true, 'url' => $s3_file];  //标记上传完成
        }
        return ['success' => true, 'url' => ''];
    }
}
