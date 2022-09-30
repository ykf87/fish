<?php

namespace App\services;

use App\Models\Course;
use App\Models\CourseVideo;

class CourseService
{
    public function info($id, $field)
    {
        $data = Course::select($field)->find($id)->toArray();
        return $data;
    }

    public function countVideo($id)
    {
        $video_total_num = CourseVideo::where('course_id', $id)->count(); //视频总数
        $video_on_num = CourseVideo::where('course_id', $id)->where('status', 1)->count(); //上架的视频总数
        $video_charge_type_1_num = CourseVideo::where('course_id', $id)->where('status', 1)->where('charge_type', 1)->count(); //已上架的免费视频数量
        $video_charge_type_2_num = CourseVideo::where('course_id', $id)->where('status', 1)->where('charge_type', 2)->count(); //已上架的收费视频数量

        if ($video_on_num > 0 && $video_on_num == $video_charge_type_2_num) {
            $charge_type = 3;
        } elseif ($video_on_num > 0 && $video_on_num == $video_charge_type_1_num) {
            $charge_type = 1;
        } elseif ($video_on_num > 0 && $video_charge_type_1_num > 0 &&  $video_charge_type_2_num > 0) {
            $charge_type = 2;
        } else {
            $charge_type = 1;
        }

        Course::where('id', $id)->update([
            'video_num' => $video_total_num,
            'charge_type' => $charge_type
        ]);

    }
}
