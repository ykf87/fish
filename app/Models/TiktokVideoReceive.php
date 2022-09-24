<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiktokVideoReceive extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'receive_time' => 'datetime:Y-m-d H:i:s'
    ];

    protected $fillable = ['pid', 'vid', 'uid', 'type', 'receive_time'];

    public function product()
    {
        return $this->belongsTo(TiktokProduct::class, 'pid');
    }

    public function video()
    {
        return $this->belongsTo(TiktokProductsVideo::class, 'vid');
    }
}
