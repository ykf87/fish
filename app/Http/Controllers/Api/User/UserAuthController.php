<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Globals\Ens;
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



        // $user = $model->find($user->id);

        if (!$nickname) {
            return $this->error('Please fill in your nickname');
        } else {
            // $user->nickname = $nickname;
        }

        if ($avatar) {

            preg_match('/^(data:\s*image\/(\w+);base64,)/', $avatar, $res);

            if (isset($res[2])) {
                $filepath     = 'avatar/'  . '1.' . $res[2];

                $content     = base64_decode(str_replace($res[1], '', $avatar));

                var_dump($filepath);
                if (Storage::disk('s3')->put($filepath, $content)) {
                    $avatarUrl     = Storage::disk('s3')->url($filepath);
                } else {
                    return $this->error('Upload image error');
                }
            } else {
                return $this->error('Wrong image');
            }
        };

        var_dump($avatarUrl);

        // $user->save();


        // return $this->success($user->toArray(), '');
    }
}
