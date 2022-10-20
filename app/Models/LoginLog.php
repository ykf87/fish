<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['uid', 'ip', 'platform', 'version', 'lang', 'region', 'device_id', 'brand', 'model', 'login_time'];

}
