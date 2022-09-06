<?php

namespace App\Admin\Actions\Tiktok;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Encore\Admin\Facades\Admin;
use App\Tiktok\Shop;
use Illuminate\Support\Arr;

use App\Models\TiktokShop;

class Getshop extends RowAction
{
    public $name = '同步店铺';
    public function dialog()
    {
        $this->confirm('确定同步?');
    }

    public function handle(Model $form)
    {
        $rs     = $form->checkAccess(Admin::user()->id);
        if ($rs !== true) { //需要重新登录
            return $this->response()->error('授权已过期,请重新授权!')->refresh();
        }
        $shop   = new Shop;
        $list   = $shop->getAuthorizedShop($form->access_token);
        if (!is_array($list)) {
            return $this->response()->error($list)->refresh();
        }

        // $list   = [
        //     ['shop_id' => '7494083165931603338', 'shop_region' => 'GB'],
        //     ['shop_id' => '8646922166412413322', 'shop_region' => 'MY'],
        //     ['shop_id' => '8646922166255061386', 'shop_region' => 'PH'],
        //     ['shop_id' => '8646918128123872650', 'shop_region' => 'SG'],
        //     ['shop_id' => '8646922166530181514', 'shop_region' => 'TH'],
        //     ['shop_id' => '8646922165822523786', 'shop_region' => 'VN']
        // ];

        $hads   = TiktokShop::where('account_id', $form->id)->pluck('id', 'shop_id')->toArray();
        $gets   = Arr::pluck($list, 'region', 'shop_id');
        // $diffAdd    = array_diff_key($gets, $hads);
        $diffDel    = array_diff_key($hads, $gets);
        if (count($diffDel) > 0) {
            TiktokShop::whereIn('id', $diffDel)->delete();
        }
        $insertArr  = [];
        foreach ($list as $item) {
            if (isset($hads[$item['shop_id']])) {
                $rrr        = [
                    'shop_region'   => $item['region'],
                    'type'          =>  $item['type'],
                    'shop_name'     => $item['shop_name'],
                ];
                TiktokShop::where('id', $hads[$item['shop_id']])
                    ->update($rrr);
            } else {
                $rrr        = [
                    'aid'           => Admin::user()->id,
                    'account_id'    => $form->id,
                    'shop_id'       => $item['shop_id'],
                    'shop_region'   => $item['region'],
                    'type'          =>  $item['type'],
                    'shop_name'     => $item['shop_name'],
                ];

                $insertArr[]    = $rrr;
            }
        }
        TiktokShop::insert($insertArr);

        $form->shop_num         = TiktokShop::where('account_id', $form->id)->count();
        $form->save();
        return $this->response()->success('更新店铺成功!')->refresh();
        // $form->checkAccess();
        // $shop->ActiveShops($form->)

        //return $this->response()->error('服务未启动...')->refresh();
        // return $this->response()->success('启动成功!')->refresh();
    }
}
