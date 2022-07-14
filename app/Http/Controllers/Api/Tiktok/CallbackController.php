<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Globals\Ens;


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
        	exit('<script>window.parent.layer.load(1);window.parent.location="/admin/tiktok-shops?code='.$code.'&aid='.$res['id'].'";</script>');
        }
	}
}
