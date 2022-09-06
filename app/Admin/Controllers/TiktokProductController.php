<?php

namespace App\Admin\Controllers;

use App\Models\TiktokAccount;
use App\Models\TiktokShop;
use App\Models\TiktokProduct;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

use App\Admin\Extensions\BatchComm;
use Illuminate\Support\Facades\DB;
use App\Admin\Actions\Post\BatchUpdateProduct;
use App\Models\TiktokShopWarehouse;

class TiktokProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Tiktok产品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TiktokProduct());
        $admin_id   = Admin::user()->id;
        $grid->model()->where('aid', $admin_id);
        if (request()->get('account_id')) {
            $grid->model()->where('account_id', request()->get('account_id'));
        }
        if (request()->get('shop_id')) {
            $grid->model()->where('shop_id', request()->get('shop_id'));
        }
        $grid->model()->orderByDesc('create_time');
        $accounts       = TiktokAccount::where('aid', $admin_id)->pluck('seller_name', 'id')->toArray();
        $shops          = TiktokShop::where('aid', $admin_id)->pluck('shop_region', 'id')->toArray();


        $grid->column('id', __('编号'))->sortable();
        $grid->column('account_id', __('授权账号'))->display(function ($val) use ($accounts) {
            return $accounts[$val] ?? $val;
        })->filter($accounts);
        $grid->column('shop_id', __('地区'))->display(function ($val) use ($shops) {
            return $shops[$val] ?? $val;
        })->filter($shops);

        $grid->column('shop.type', __('产品类型'))->using(TiktokShop::$type)->filter(TiktokShop::$type);

        $grid->column('pid', __('产品id'))->hide();
        $grid->column('name', __('产品名称'))->display(function ($val) {
            $len        = mb_strlen($val, 'utf-8');
            $str        = $val;
            $maxlen     = 20;
            if ($len >= 20) {
                $str    = mb_substr($val, 0, ($maxlen - 1), 'utf-8') . '...';
            }
            return '<a title="' . $val . '">' . $str . '</a>';
        })->filter('like');
        $grid->column('thumbs', __('图集'))->carousel(100, 100);
        $grid->column('create_time', __('上架时间'))->display(function ($val) {
            return $val ? date('Y-m-d H:i:s', $val) : '';
        })->filter('range', 'datetime');
        $grid->column('status', __('状态'))->using(TiktokProduct::$status)->filter(TiktokProduct::$status)->label(TiktokProduct::$statusLabel);
        $grid->column('currency', __('货币'));
        $grid->column('maxprice', __('最高价'))->filter('range')->sortable();
        $grid->column('minprice', __('最低价'))->filter('range')->sortable();
        $grid->column('commission', __('佣金比例'))->display(function ($val) {

            return $val ? ($val * 100) . '%' : '';
        })->filter('range')->sortable()->editable();
        $grid->column('stocks', __('总库存'))->filter('range');
        $grid->column('sales', __('销量'))->filter('range')->sortable()->editable();
        $grid->column('gmv', __('销售额'))->filter('range')->sortable();
        $grid->column('commissioned', __('产生佣金'))->filter('range')->sortable();


        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
        });
        $grid->tools(function ($tools) {
            $tools->append(new BatchComm());
        });
        $grid->batchActions(function ($batch) {
            $batch->add(new BatchUpdateProduct());
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    // protected function detail($id)
    // {
    //     $show = new Show(TiktokProduct::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('aid', __('Aid'));
    //     $show->field('account_id', __('Account id'));
    //     $show->field('shop_id', __('Shop id'));
    //     $show->field('pid', __('Pid'));
    //     $show->field('name', __('Name'));
    //     $show->field('create_time', __('Create time'));
    //     $show->field('status', __('Status'));
    //     $show->field('maxprice', __('Maxprice'));
    //     $show->field('minprice', __('Minprice'));
    //     $show->field('commission', __('Commission'));
    //     $show->field('currency', __('Currency'));
    //     $show->field('stocks', __('Stocks'));

    //     return $show;
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TiktokProduct());

        // $form->number('aid', __('Aid'));
        // $form->number('account_id', __('Account id'));
        // $form->number('shop_id', __('Shop id'));
        // $form->text('pid', __('Pid'));
        // $form->text('name', __('Name'));
        // $form->number('create_time', __('Create time'));
        // $form->switch('status', __('Status'));
        // $form->decimal('maxprice', __('Maxprice'));
        // $form->decimal('minprice', __('Minprice'));
        $form->decimal('commission', __('Commission'));
        $form->decimal('sales', __('Commission'));
        // $form->text('currency', __('Currency'));
        // $form->number('stocks', __('Stocks'));

        return $form;
    }

    //设置佣金
    public function commission(Request $request)
    {
        $newComm        = (int) $request->input('commission');
        $commTo         = (int) $request->input('commission_to');
        $minprice       = (float) $request->input('minprice');
        $maxprice       = (float) $request->input('maxprice');
        $minstock       = (int) $request->input('minstock');
        $maxstock       = (int) $request->input('maxstock');
        $mincomm        = (int) $request->input('mincomm');
        $maxcomm        = (int) $request->input('maxcomm');
        $account_id     = (int) $request->input('account_id');
        $shop_id        = (int) $request->input('shop_id');
        $productid      = trim($request->input('product_id'));
        $productname    = trim($request->input('product_name'));
        if ($newComm <= 0 || $newComm >= 100) {
            return response()->json([
                'code'  => 500,
                'msg'   => '佣金设置错误,请输入1-100整数!',
            ]);
        }
        $canChange  = false;

        $model      = new TiktokProduct;
        // $model      = $model->query();
        if ($minprice > 0) {
            $canChange  = true;
            $model      = $model->where('minprice', '>=', $minprice);
        }
        if ($maxprice > 0) {
            $canChange  = true;
            $model      = $model->where('maxprice', '<=', $maxprice);
        }
        if ($minstock > 0) {
            $canChange  = true;
            $model      = $model->where('stocks', '>=', $minstock);
        }
        if ($maxstock > 0) {
            $canChange  = true;
            $model      = $model->where('stocks', '<=', $maxstock);
        }
        if ($mincomm > 0) {
            $canChange  = true;
            $model      = $model->where('commission', '>=', (float) ($mincomm / 100));
        }
        if ($maxcomm > 0) {
            $canChange  = true;
            $model      = $model->where('commission', '<=', (float) ($maxcomm / 100));
        }
        if ($account_id > 0) {
            $canChange  = true;
            $model      = $model->where('account_id', $account_id);
        }
        if ($shop_id > 0) {
            $canChange  = true;
            $model      = $model->where('shop_id', $shop_id);
        }
        if ($productid) {
            $productid  = str_replace(' ', '', $productid);
            $pids       = explode(',', $productid);
            $pidsInt    = [];
            foreach ($pids as $pppid) {
                if (strpos($pppid, '-') !== false) {
                    $tmp    = explode('-', $pppid);
                    if (isset($tmp[1])) {
                        $start  = (int) $tmp[0];
                        $end    = (int) $tmp[1];
                        for (; $start <= $end; $start++) {
                            $pidsInt[]  = $start;
                        }
                    }
                } else {
                    $tmp    = (int) $pppid;
                    if ($tmp > 0) {
                        $pidsInt[]  = $tmp;
                    }
                }
            }
            if (count($pidsInt) > 0) {
                $canChange  = true;
                $model      = $model->whereIn('id', $pidsInt);
            }
        }
        if ($productname) {
            $canChange  = true;
            $model      = $model->where('name', 'like', "%$productname%");
        }
        if ($canChange !== true) {
            return response()->json([
                'code'  => 500,
                'msg'   => '请设置条件,不允许没有任何条件设置佣金!',
            ]);
        }

        if ($commTo > 0 && $commTo > $newComm) {
            $from       = (float) ($newComm / 100);
            $to         = (float) ($commTo / 100);

            $sql        = str_replace('?', '"%s"', $model->toSql());
            $sql        = str_replace('select * from ', 'update ', $sql);
            $sql        = str_replace('where', ' set `commission` = cast(rand()*(' . $to . '-' . $from . ')+' . $from . ' as decimal(18,2)) where', $sql);
            $params     = $model->getBindings();
            $sqlr       = sprintf($sql, ...$params);

            $rest       = DB::update($sqlr);
        } else {

            $setTo      = (float) ($newComm / 100);
            $rest       = $model->update(['commission' => $setTo]);
            // dd($rest);
        }

        if ($rest) {
            $sql        = 'update tiktok_products set commission_price = (maxprice*commission) where commission_price is null and commission > 0 and commission<1';
            \DB::connection()->enableQueryLog();
            DB::unprepared($sql);
            $sqllog = \DB::getQueryLog();
            dd($sqllog);
            return response()->json([
                'code'  => 200,
                'msg'   => '修改成功!',
            ]);
        }
        return response()->json([
            'code'  => 500,
            'msg'   => '修改失败!',
        ]);
    }
}
