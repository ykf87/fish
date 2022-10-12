<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CourseRequest extends BaseRequest
{
    public function ruleCourseList()
    {
        return [
            'category_id' => 'int',
            'page' => 'int|min:1',
            'limit' => 'int|min:1|max:50',
            'keyword' => 'min:2'
        ];
    }

    public function ruleVideoList()
    {
        return [
            'course_id' => 'required|int',
            'page' => 'int|min:1',
            'limit' => 'int|min:1|max:50',
            'keyword' => 'min:2'
        ];
    }
}
