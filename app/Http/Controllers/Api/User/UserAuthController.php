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
        $model             = new User();




        $user = $model->find($user->id);

        if ($nickname) {
            $user->nickname = $nickname;
            $success['nickname'] = $nickname;
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
        };
        $user->avatar =   $filepath;
        $user->save();

        $success['avatar'] = env('AWS_URL') . '/' . env('AWS_BUCKET') . '/' . $user->avatar;

        return $this->success($success, '');
    }
}
