<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokProductsVideo extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static $type 	= [
        'original'	=> '原始视频',
        'clip'	=> '剪辑视频',
    ];

    public static $typeLabel 	= [
        'original'	=> 'default',
        'clip'	=> 'success',
    ];

    public function product()
    {
        return $this->belongsTo(TiktokProduct::class, 'pid');
    }
}
