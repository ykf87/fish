<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseViewLog extends Model{
	use HasFactory;
	public $timestamps = false;

	/**
	 * 添加课程访问日志
	 * @param int $uid 	用户id
	 * @param int $cid 	课程id
	 * @param int $cvid 视频id
	 */
	public static function addlog(int $uid, int $cid, int $cvid = 0){
		if($uid < 1 || $cid < 1){
			return;
		}
		$row 	= self::where('uid', $uid)->where('course_id', $cid)->first();
		if($row){
			$row->addtime 				= time();
			if($cvid > 0){
				$row->course_video_id 	= $cvid;
			}
		}else{
			$row 					= new self;
			$row->uid 				= $uid;
			$row->course_id 		= $cid;
			$row->course_video_id 	= $cvid;
			$row->addtime 			= time();
		}
		$row->save();
		return $row->course_video_id;
	}
}
