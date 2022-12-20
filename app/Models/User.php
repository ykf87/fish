<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\SerializeDate;
use App\Globals\Ens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SerializeDate;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nickname',
        'email',
        'password',
        'register_ip',
        'relation',
        'parent_invite',
        'pid',
        'inviteurl',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'relation',
        'singleid',
        // 'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    public static function getTokenUser(){
        $request    = request();
        $token      = $request->header('token') or $request->cookie('token') or $request->input('token');
        if ($token) {
            $info   = base64_decode(Ens::decrypt($token));
            $info   = $info ? json_decode($info, true) : null;
            if(isset($info['id'])){
                return self::find($info['id']);
            }
        }
        return null;
    }

    public function getEmailAttribute($val){
        $tmp    = explode('@', $val);
        $tmp[0] = substr($tmp[0], 0, 2) . '***' . substr($tmp[0], -2);
        return implode('@', $tmp);
    }

    public function getAvatarAttribute($val){
        return ($val && strpos($val, 'http') === false) ? Storage::disk('s3')->url($val) : $val;
    }
}
