<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CourseOrder;
use App\Globals\Ens;
use Aws\S3\S3Client;
use Encore\Admin\Grid\Displayers\Limit;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;
use App\Models\CourseViewLog;
use App\Models\Commission;

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

        $info = User::find($user->id);

        // $info['avatar'] = $info['avatar'] ? env('AWS_URL') . '/' . env('AWS_BUCKET') . '/' . $info['avatar'] : '';
        // if(!$info->inviteurl){
        //     $info->inviteurl = route('api.invi', ['invo' => $user->invitation_code]);
        // }
        $info->invite = route('api.invi', ['invo' => $user->invitation_code]);

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

            if(!$this->verifyCode($mail, $mailcode)){
                return $this->error('Verification code error');
            }
            // 校验验证码
            // $verify = Redis::get($mail);
            // if (!$verify || $mailcode != $verify) {
            //     return $this->error('Verification code error');
            // }
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
            $success['avatar'] = $user->avatar;
        };

        $user->save();


        return $this->success($success, '');
    }

    /**
     * 获取课程购买记录
     */
    public function buied(Request $request){
        $user   = $request->get('_user');

        $page   = $request->input('page', 1);
        $limit  = $request->input('limit', 10);

        $query  = CourseOrder::from('course_orders as a')
            ->select('a.id', 'a.price', 'a.course_id', 'b.title', 'b.pic', 'b.video_num', 'b.views')
            ->leftJoin('courses as b', 'b.id', '=', 'a.course_id')
            ->where('a.uid', $user->id);


        $list   = $query->offset(($page-1)*$limit)->limit($limit)->orderByDesc('a.addtime')->get();
        foreach($list as &$val){
            if($val->pic){
                $val->pic   = env('AWS_URL') . '/' . env('AWS_BUCKET') . '/' . $val->pic;
            }
        }

        return $this->success(['page' => $page, 'limit' => $limit, 'total_limit' => $query->count(), 'lists' => $list]);
    }

    public function viewed(Request $request){
        $user   = $request->get('_user');

        $page   = $request->input('page', 1);
        $limit  = $request->input('limit', 10);

        $query  = CourseViewLog::from('course_view_logs as a')
            ->select('a.id', 'b.price', 'a.course_id', 'b.title', 'b.pic', 'b.video_num', 'b.views')
            ->leftJoin('courses as b', 'b.id', '=', 'a.course_id')
            ->where('a.uid', $user->id);


        $list   = $query->offset(($page-1)*$limit)->limit($limit)->orderByDesc('a.addtime')->get();
        foreach($list as &$val){
            if($val->pic){
                $val->pic   = env('AWS_URL') . '/' . env('AWS_BUCKET') . '/' . $val->pic;
            }
        }
        return $this->success(['page' => $page, 'limit' => $limit, 'total_limit' => $query->count(), 'lists' => $list]);
    }

    private function verifyCode($mail, $code){
        if(!trim($code)){
            return false;
        }
        if(env('APP_DEBUG') && env('CODE') && $code == env('CODE')){
            return true;
        }
        return $code == Redis::get($mail);
    }

    /**
     * 获取我的邀请列表
     */
    public function invites(Request $request){
        // Commission::recharge(CourseOrder::find(1));
        // dd('111');
        $user       = $request->get('_user');
        $page       = (int)$request->input('page');
        $limit      = (int)$request->input('limit');
        if($page < 1) $page = 1;
        if($limit < 1) $limit = 10;

        $type       = (int)$request->input('type');
        $dbs        = User::select('nickname', 'email', 'avatar', 'id', 'invitation_code as invite');
        switch($type){
            case 0:
                $dbs    = $dbs->where('pid', $user->id);
                break;
            case 1:
                $dbs    = $dbs->where('pid', '!=', $user->id)->whereRaw("find_in_set('$user->id', relation)");
                break;
            default:
                $dbs    = $dbs->whereRaw("find_in_set('$user->id', relation)");
        }
        $arr        = [
            'page'          => $page,
            'limit'         => $limit,
            'total_limit'   => $dbs->count(),
            'lists'         => $dbs->orderByDesc('id')->offset(($page-1)*$limit)->limit($limit)->get(),
        ];
        return $this->success($arr);
    }

    /**
     * 我的佣金明细
     */
    public function commission(Request $request){
        $user       = $request->get('_user');
        $page       = (int)$request->input('page');
        $limit      = (int)$request->input('limit');
        if($page < 1) $page = 1;
        if($limit < 1) $limit = 10;

        $dbs        = Commission::select('commissions.geted as access', 'commissions.addtime', 'commissions.status', 'commissions.id', 'u.nickname', 'u.avatar')->leftJoin('users as u', 'u.id', '=', 'commissions.uid')->where('commissions.uid', '=', $user->id);
        $total      = $dbs->count();
        $lists      = $dbs->offset(($page-1)*$limit)->limit($limit)->orderByDesc('addtime')->get()->toArray();

        foreach($lists as &$item){
            $item['access']   = (float)$item['access'];
        }

        $arr        = [
            'page'          => $page,
            'limit'         => $limit,
            'total_limit'   => $total,
            'lists'         => $lists,
        ];
        return $this->success($arr);
    }
}
