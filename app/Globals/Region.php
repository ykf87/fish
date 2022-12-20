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
            $arr    = [
                'iso'       => $record->country->isoCode,
                'country'   => $record->country->name,
                'city'      => $record->city->name,
                'lat'       => $record->location->latitude,
                'lon'       => $record->location->longitude,
                'timezone'  => $record->location->timeZone,
            ];
        } catch (\Exception $e) {
            $arr    = [
                'iso'       => 'us',
                'country'   => 'United States',
                'city'      => 'Washington',
                'lat'       => '38.00000000',
                'lon'       => '-97.00000000',
                'timezone'  => 'America/Los_Angeles',
            ];
        }

        
        return $arr;
    }
}
