<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Globals\Ens;


class CallbackController extends Controller{
	public function index(Request $request){
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
        	exit('<script>window.parent.layer.closeAll();window.parent.location="/admin/tiktok-shops/addnew?code='.$code.'&id='.$res['id'].'";</script>');
        }
        //getshopauth("'.$code.'", '.$res['id'].')
        // $goto 	= secure_url('admin/tiktok-shops/addnew?' . http_build_query($arr));
        // // dd($goto);
        // header('location:'.$goto);
	}
}
