<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Facades\Admin as uadmin;

use App\Models\TiktokAccount;
use App\Models\TiktokShop;

class BatchComm extends AbstractTool{
	protected function script(){
        $admin_id       = uadmin::user()->id;
        $accounts       = TiktokAccount::where('aid', $admin_id)->pluck('seller_name', 'id')->toArray();
        $shops          = TiktokShop::where('aid', $admin_id)->pluck('shop_region', 'id')->toArray();
        // $html           = str_replace("\r\n", '', view('admin.tiktok.setcomm', [
        //     'accounts'  => $accounts,
        //     'shops'     => $shops,
        // ]));
        $html           = view('admin.tiktok.setcomm', [
            'accounts'  => $accounts,
            'shops'     => $shops,
        ]);
        uadmin::html(response($html)->getContent());
        return <<<EOT
$('.addshopbtn').click(function () {
    layer.open({
        title: '根据以下条件设置产品佣金!',
        type: 1,
        area: ['40%', '80%'],
        content: $('.nllppsdf:eq(0)').html(),
        btn: ['设置'],
        yes: function(i, o){
            var pp = $(o).find('form');
            var yongjin     = pp.find('input[name=commission]');
            var yjval       = parseInt(yongjin.val());
            if(!yjval || yjval <= 0 || yjval >= 100){
                layer.msg('请正确填写佣金!');
                yongjin.focus();
                return false;
            }
            var cannext = false;
            pp.find('.checks').each(function(){
                if($(this).val()){
                    cannext = true;
                    return false;
                }
            });
            if(cannext == false){
                layer.msg('请设置条件!');
                return false;
            }
            var confirmind = layer.confirm('确定修改吗?', function(){
                console.log(pp.serialize());
                layer.close(confirmind);
                var loadingind = layer.load(2);
                $.post('/admin/tiktok-products/commission', pp.serialize(), function(res){
                    layer.close(loadingind);
                    if(res.code != 200){
                        layer.msg(res.msg);
                        return false;
                    }
                    history.go(0);
                });
            });
        }
    });
    return false;
});


EOT;
    }

    public function render()
    {
        Admin::headerJs(asset('layer/layer.js'));
        Admin::script($this->script());

        return view('admin.tiktok.comm');
    }
}