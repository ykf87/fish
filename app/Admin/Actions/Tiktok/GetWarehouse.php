<?php

namespace App\Admin\Actions\Tiktok;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Facades\Admin;
use App\Tiktok\Shop;
use App\Models\TiktokAccount;
use App\Models\TiktokShopWarehouse;
use Illuminate\Support\Arr;


class GetWarehouse extends RowAction
{
    public $name = '获取仓库';
    public function dialog()
    {
        $this->confirm('确定拉取?');
    }


    public function handle(Model $form)
    {
        $shopId     = $form->shop_id;
        $model      = TiktokAccount::find($form->account_id);
        $rs         = $model->checkAccess(Admin::user()->id);
        if ($rs !== true) { //需要重新登录
            return $this->response()->error('授权已过期,请重新授权!')->refresh();
        }
        $shop   = new Shop;

        $list   = $shop->getWarehouseList($model->access_token,  $shopId);

        if (!is_array($list)) {
            return $this->response()->error($list)->refresh();
        }

        $hads   = TiktokShopWarehouse::where('shop_id',  $form->id)->pluck('id', 'warehouse_id')->toArray();
        $gets   = Arr::pluck($list, 'warehouse_name', 'warehouse_id');
        $diffAdd    = array_diff_key($gets, $hads);
        $diffDel    = array_diff_key($hads, $gets);
        if (count($diffAdd) > 0) {
            TiktokShopWarehouse::whereIn('id', $diffDel)->delete();
        }

        if (count($diffAdd) > 0) {
            $insertArr  = [];
            foreach ($diffAdd as $key => $value) {
                $rrr = [];
                foreach ($list as $item) {
                    if ($key ==  $item['warehouse_id']) {
                        $rrr        = [
                            'aid'           => Admin::user()->id,
                            'account_id'    => $form->account_id,
                            'shop_id'       =>  $form->id,
                            'warehouse_id'           => $item['warehouse_id'],
                            'warehouse_name'    => $item['warehouse_name'],
                            'warehouse_effect_status'       => $item['warehouse_effect_status'],
                            'warehouse_type'   => $item['warehouse_type'],
                            'warehouse_sub_type'           => $item['warehouse_sub_type'],
                            'region'    => $item['warehouse_address']['region'],
                            'region_code'       =>  $item['warehouse_address']['region_code'],
                            'state'   => $item['warehouse_address']['state'],
                            'city'           => $item['warehouse_address']['city'],
                            'district'    => $item['warehouse_address']['district'],
                            'town'       =>  isset($item['warehouse_address']['town']) ? $item['warehouse_address']['town'] : '',
                            'phone'           => $item['warehouse_address']['phone'],
                            'contact_person'    => $item['warehouse_address']['contact_person'],
                            'full_address'       =>  $item['warehouse_address']['full_address'],

                        ];
                    }
                }
                $insertArr[]    = $rrr;
            }

            TiktokShopWarehouse::insert($insertArr);
        }

        return $this->response()->success('获取店铺仓库信息成功')->refresh();
    }
}
