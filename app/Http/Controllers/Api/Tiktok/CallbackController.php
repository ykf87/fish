<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Globals\Ens;

use App\Models\TiktokProduct;

class CallbackController extends Controller{
	public function index(Request $request){
		echo '<center style="padding: 50px 0;">授权处理中.....</center>';
		$state      = $request->get('state');
        $code       = $request->get('code');
        if(!$code || !$state){
            return abort(404);
        }
        $str        = Ens::decrypt(base64_decode($state));
        $res        = json_decode($str, true);
        if(!isset($res['id']) || !isset($res['time'])){
            return abort(404);
        }
        if((time()-$res['time']) > 60000){
            exit('<script>window.parent.layer.closeAll();window.parent.layer.msg("授权超时!");</script>');
        }else{
        	exit('<script>window.parent.layer.load(1);window.parent.location="/admin/tiktok-account?code='.$code.'&aid='.$res['id'].'";</script>');
        }
	}

    //产品详情
    public function proinfo(Request $request){
        $data   = $request->input('data');
        if(!$data){
            return;
        }
        $data   = json_decode($data, true);
        $data   = $data['data'] ?? null;
        if(!$data){
            return;
        }

        TiktokProduct::updFromTiktok($data);
        // file_put_contents(__DIR__ . '/1.txt', $data['product_id']);
    }
}