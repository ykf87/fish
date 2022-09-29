<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
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
        '2' => '部分收费',
        '3' => '全部收费',
    ];

    public static $chargeTypeLabel = [
        '1' => 'default',
        '2' => 'info',
        '3' => 'success',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
