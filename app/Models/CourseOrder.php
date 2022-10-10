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
        'payer_info' => 'json',
    ];

    public $timestamps = false;
}
