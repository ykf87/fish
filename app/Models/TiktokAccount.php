<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Tiktok\Shop;

class TiktokAccount extends Model{
	use HasFactory;

	//add or update
	public function au($data, $adminid){
        $shopId     = $data['open_id'] ?? null;
        if(!$shopId){
            return '获取店铺失败!';
        }

        $arr        = [
            'status'    => 1
        ];
        if(isset($data['access_token'])){
            $arr['access_token']                    = $data['access_token'];
        }
        if(isset($data['access_token_expire_in'])){
            $arr['access_token_expire_in']          = $data['access_token_expire_in'];
        }
        if(isset($data['refresh_token'])){
            $arr['refresh_token']                   = $data['refresh_token'];
        }
        if(isset($data['refresh_token_expire_in'])){
            $arr['refresh_token_expire_in']         = $data['refresh_token_expire_in'];
        }
        if(isset($data['seller_name'])){
            $arr['seller_name']                     = $data['seller_name'];
        }
        if(isset($data['region'])){
            $arr['region']                          = $data['region'];
        }

        $row        = self::where('aid', $adminid)->where('open_id', $shopId)->first();
        $update 	= false;
        if($row){//有就更新
        	$update 		= true;
        	foreach($arr as $k => $v){
        		$row->{$k} 	= $v;
        	}
        }else{
        	$arr['aid']		= $adminid;
        	$arr['open_id']	= $shopId;
        	$row 	= new self;
        	foreach($arr as $k => $v){
        		$row->{$k} 	= $v;
        	}
        }
        if($row->save()){
            foreach($arr as $k => $v){
                $this->{$k}  = $v;
            }
        	return $update ? '更新店铺成功!' : '添加店铺成功!';
        }else{
        	return $update ? '更新店铺失败!' : '添加店铺失败!';
        }
	}

    public function checkAccess($adminid){
        $now    = time();
        $shop   = new Shop;
        if($this->access_token_expire_in <= $now){
            if($this->refresh_token_expire_in <= $now){//如果刷新token都过期,那么要重新登录
                return false;
            }else{
                $res        = $shop->refreshaccesstoken($this->refresh_token);
                $res        = json_decode($res, true);
                if(!isset($res['data'])){
                    return false;
                }
                $rrs        = $this->au($res['data'], $adminid);
                if(strpos($rrs, '成功') < 0){
                    return false;
                }
            }
        }
        return true;
    }
}
