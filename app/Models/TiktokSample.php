<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokSample extends Model{
	use HasFactory;
	public $timestamps = false;
	protected $casts = [
	    'addtime' => 'datetime:Y-m-d H:i:s',
	    'needtime' => 'datetime:Y-m-d H:i:s',
    ];

    public static $status 	= [
        0	=> '待审核',
        1	=> '审核通过寄样中',
        2	=> '已发货',
        3	=> '已收货',
        -1	=> '拒绝',
        -2	=> '用户取消',
    ];

    public static $statusLabel 	= [
        0	=> 'default',
        1	=> 'info',
        2	=> 'success',
        3	=> 'primary',
        -1	=> 'danger',
        -2	=> 'warning',
    ];

    public function darren()
    {
        return $this->belongsTo(TiktokDarren::class);
        
	}
}
