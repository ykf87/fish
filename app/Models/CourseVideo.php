<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseVideo extends Model
{
    use HasFactory;

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
}
