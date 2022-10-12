<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no', 'uid', 'course_id', 'product', 'description', 'payment_type',
        'currency', 'price', 'shipping', 'total', 'pay_id', 'status', 'addtime'];

    protected $casts = [
        'addtime' => 'date:Y-m-d H:i:s',
        'paytime' => 'date:Y-m-d H:i:s',
        'payer_info' => 'json',
    ];

    public $timestamps = false;

    public static $status = [
        '1' => '待付款',
        '20' => '已付款'
    ];

    public static $statusLabel = [
        '1' => 'default',
        '20' => 'success'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }
}
