<?php

namespace App\Admin\Controllers;

use App\Models\TiktokAccount;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use App\Tiktok\Shop;
use Encore\Admin\Admin as SAdmin;

use App\Admin\Extensions\AuthTiktok;
use App\Admin\Actions\Tiktok\Getshop;
use App\Safety\Aess;


class TiktokaccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'TikTok账号管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        // $res    = Aess::decode(base64_decode('Z3llMXlzZkl6QmVKS0ZhUldlRWJabUpyRXZHSE5HM3p0SmtrS0x2VG9RNThoZGRmTVQ3MFZFVFREOXNmSHY1Vg=='));
        // dd($res);
        // $shop   = new Shop;
        // print_r($shop->ActiveShops('ROW_fMKaBwAAAADXLEz_KeZemMx3gcCIoaPTk5xGimWCw3EUdZE_z9cC1ikyIBR6buCF1a1irhiiZ_rQFwZ2lgJDnz9duR8x9fWC-sHD54GPYlcS4cH1uUw5SvXjEQn-JPMpUdE4_pTgCSOhhaFhkhfBU-LMzmRwKoib'));
        $request    = request();
        $code   = $request->get('code');
        $admin  = $request->get('aid');
        $err    = $request->get('err');
        $loginId    = Admin::user()->id;
        $script     = '';
        if($code){
            if($loginId != $admin){
                $script = 'layer.closeAll();layer.msg("不正确!");';
            }else{
                $shop   = new Shop;
                $ress   = $shop->accesstoken($code);
                $res    = json_decode($ress, true);
                if(!isset($res['code']) || !isset($res['data'])){
                    $script = 'layer.closeAll();layer.msg("获取店铺信息失败!");';
                }else{
                    $ta     = new TiktokAccount;
                    $res    = $ta->au($res['data'], $loginId);
                    $script = 'layer.closeAll();layer.msg("'.$res.'");setTimeout(function(){history.go(-1);}, 3000);';
                }
            }
        }

        $grid = new Grid(new TiktokAccount());
        $grid->model()->where('aid', $loginId);

        $grid->column('id', __('编号'));
        $grid->column('region', __('账号地区'))->display(function($val){
            return Shop::$resion[$val] ?? $val;
        });
        $grid->column('open_id', __('ID'));
        $grid->column('seller_name', __('店铺名称'));
        $grid->column('shop_num', __('店铺数量'))->display(function($val){
            return '<a href="'.route('admin.tiktok-shops.index', ['account_id' => $this->id]).'">' . $val . '</a>';
        });
        $grid->column('product_num', __('商品数量'));
        $grid->column('status', __('状态'));
        $grid->column('created_at', __('创建日期'))->display(function($val){
            return date('Y-m-d H:i:s', strtotime($val));
        });
        $grid->column('refresh_token_expire_in', __('授权到期日期'))->display(function($val){
            return date('Y-m-d H:i:s', $val);
        });

        $grid->disableCreateButton();
        $grid->disableExport();


        $grid->tools(function ($tools) {
            $tools->append(new AuthTiktok());
        });
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
            // dd($actions->getAttribute('id'));
            $actions->add(new Getshop);
        });
        if($script){
            SAdmin::script($script);
        }
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
        // $show = new Show(TiktokShop::findOrFail($id));

        // $show->field('id', __('Id'));
        // $show->field('aid', __('Aid'));
        // $show->field('region', __('Region'));
        // $show->field('shop_id', __('Shop id'));
        // $show->field('shop_name', __('Shop name'));
        // $show->field('type', __('Type'));
        // $show->field('status', __('Status'));
        // $show->field('created_at', __('Created at'));
        // $show->field('updated_at', __('Updated at'));

        // return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        // $form = new Form(new TiktokShop());

        // $form->number('aid', __('Aid'));
        // $form->text('region', __('Region'));
        // $form->text('shop_id', __('Shop id'));
        // $form->text('shop_name', __('Shop name'));
        // $form->text('type', __('Type'));
        // $form->switch('status', __('Status'))->default(1);

        // return $form;
    }
}
