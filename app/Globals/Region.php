<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/10/21
 * Time: 14:01
 */

namespace App\Globals;

use Illuminate\Support\Facades\Storage;
use GeoIp2\Database\Reader;

class Region{
    //根据ip获取地理位置信息
    public static function GetRegionByIp($ip){
        try {
            $reader = new Reader(Storage::disk('public')->path('GeoLite2-City.mmdb'));
            $record = $reader->city($ip);
        } catch (\Exception $e) {
            return false;
        }

        $arr 	= [
            'iso'		=> $record->country->isoCode,
            'country'	=> $record->country->name,
            'city'		=> $record->city->name,
            'lat'		=> $record->location->latitude,
            'lon'		=> $record->location->longitude
        ];
        return $arr;
    }
}
