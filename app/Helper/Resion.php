<?php
namespace App\Helper;

use Illuminate\Support\Facades\Storage;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Log;

class Resion{
	//根据ip获取地理位置信息
	public static function GetResionByIp($ip){
		try {
			$reader = new Reader(Storage::disk('local')->path('GeoLite2-City.mmdb'));
			$record = $reader->city($ip);
			$arr 	= [
				'iso'		=> strtolower($record->country->isoCode),
				'country'	=> $record->country->name,
				'city'		=> $record->city->name,
				'lat'		=> $record->location->latitude,
				'lon'		=> $record->location->longitude,
				'timezone'	=> $record->location->timeZone,
			];
		} catch (\Exception $e) {
			if(env('APP_DEBUG') === false){
				Log::channel('daily')->error($e->getMessage());
			}
			$arr 	= [
				'iso'		=> 'us',
				'country'	=> 'United States',
				'city'		=> 'Allison',
				'lat'		=> '0',
				'lon'		=> '0',
				'timezone'	=> 'America/Chicago',
			];
		}
		return $arr;
	}
}