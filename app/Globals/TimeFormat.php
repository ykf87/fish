<?php
namespace App\Globals;
use IntlDateFormatter;

class TimeFormat{
	private static $timezone 	= null;
	private static $lang 		= null;
	public static function output(int $time, string $fmt = 'datetime', string $timezone = '', string $lang = ''){
		if(!$timezone){
			if(!self::$timezone){
				self::$timezone 	= request()->get('_resion')['timezone'] ?? 'PRC';
			}
		}else{
			self::$timezone 		= $timezone;
		}
		if(!$lang){
			if(!self::$lang){
				self::$lang 	= request()->get('_lang')['code'] ?? null;
			}
		}else{
			self::$lang 		= $lang;
		}

		switch ($fmt) {
			case 'time':
				$obj = new IntlDateFormatter(self::$lang,IntlDateFormatter::NONE, IntlDateFormatter::MEDIUM, 
    self::$timezone,IntlDateFormatter::GREGORIAN);
				break;
			case 'date':
				$obj = new IntlDateFormatter(self::$lang,IntlDateFormatter::SHORT, IntlDateFormatter::NONE, 
    self::$timezone,IntlDateFormatter::GREGORIAN);
				break;
			default:
				$obj = new IntlDateFormatter(self::$lang,IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM, 
    self::$timezone,IntlDateFormatter::GREGORIAN);
				break;
		}
		return $obj->format($time);
	}
}
