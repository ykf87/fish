<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\services\CategoryService;
use App\services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    //课程分类列表
    public function category(CategoryService $categoryService)
    {
        $list = $categoryService->getListByMark('video_course');
        return $this->success($list);
    }

    //课程列表
    public function courseList(CourseRequest $request)
    {
        $param = $request->all();
        $data = $this->courseService->courseList($param);

        return $this->success($data);
    }

    //视频列表
    public function videoList(CourseRequest $request)
    {
        $param = $request->all();
        $data = $this->courseService->videoList($param);

        return $this->success($data);
    }
    
    //视频详情
    public function videoInfo($id, Request $request)
    {
        $user = $request->get('_user');
        $info = $this->courseService->videoInfo($id, $user->id);

        if ($info['success']) {
            return $this->success($info['msg']);
        } else {
            return $this->error($info['msg']);
        }
    }
}
