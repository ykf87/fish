<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Globals\Ens;
use Aws\S3\S3Client;
use Encore\Admin\Grid\Displayers\Limit;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;

class UserAuthController extends Controller
{
    /**
     * 获取用户信息
     *
     * @param Request $request
     * @return void
     */
    public function GetUserInfo(Request $request)
    {
        $user         = $request->get('_user');

        $info = User::find($user->id)->toArray();

        if (file_exists('../.env')) {
            $info['avatar'] = env('AWS_URL') . '/' . env('AWS_BUCKET') . '/' . $info['avatar'];
        }

        return $this->success($info, '');
    }

    /**
     * 修改用户信息
     *
     * @param Request $request
     * @return void
     */
    public function updateUser(Request $request)
    {
        $user         = $request->get('_user');

        $nickname          = $request->input('nickname');
        $avatar            = $request->input('avatar');
        $mail              = $request->input('mail');
        $mailcode          = $request->input('mailcode');
        $password          = $request->input('password');
        $model             = new User();
        $success = [];




        $user = $model->find($user->id);

        if ($nickname) {
            $user->nickname = $nickname;
            $success['nickname'] = $nickname;
        }

        if ($mail) {
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                return $this->error('The email address is invalid');
            }
            // 邮箱唯一性验证
            $count = $model->where('email', $mail)->count();
            if ($count) {
                return $this->error('The email address has been registered');
            }
            // 校验验证码
            $verify = Redis::get($mail);
            if (!$verify || $mailcode != $verify) {
                return $this->error('Verification code error');
            }
            //密码校验
            if ($password) {
                if (!Hash::check($password, $user->password)) {
                    return $this->error('passwordWrong');
                }
            }
            $success['mail'] = $mail;
            $user->email = $mail;
        }

        if ($avatar) {

            preg_match('/^(data:\s*image\/(\w+);base64,)/', $avatar, $res);

            if (isset($res[2])) {
                $filepath     = 'avatar/'  . $user->id . '.' . $res[2];

                $content     = base64_decode(str_replace($res[1], '', $avatar));

                if (Storage::disk('s3')->put($filepath, $content)) { } else {
                    return $this->error('Upload image error');
                }
            } else {
                return $this->error('Wrong image');
            }
            $user->avatar =   $filepath;
            $success['avatar'] = env('AWS_URL') . '/' . env('AWS_BUCKET') . '/' . $user->avatar;
        };

        $user->save();


        return $this->success($success, '');
    }
}
