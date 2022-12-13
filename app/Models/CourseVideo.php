<?php

namespace App\Models;

use App\services\CourseService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseVideo extends Model
{
    use HasFactory;

    protected $appends = ['full_pic_url'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static $status = [
        '-1' => '已下架',
        '1' => '已上架',
    ];

    public static $statusLabel = [
        '-1' => 'default',
        '1' => 'success',
    ];

    public static $chargeType = [
        '1' => '免费',
        '2' => '收费',
    ];

    public static $chargeTypeLabel = [
        '1' => 'default',
        '2' => 'success',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public static function boot()
    {
        parent::boot();
        static::saved(function ($model) {
            $courseService = new CourseService();
            $courseService->countVideo($model->course_id);
        });
        static::deleted(function ($model) {
            $courseService = new CourseService();
            $courseService->countVideo($model->course_id);
        });
    }

    public function getFullPicUrlAttribute()
    {
        $url = '';
        if (!empty($this->pic)) {
            $url = env('AWS_URL') . '/' . $this->pic;
        }
        return $url;
    }

//    public function getVideoUrlAttribute($value)
//    {
//        return env('AWS_URL') . '/' . env('AWS_BUCKET') . '/' . $value;
//    }
}
