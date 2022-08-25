<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Globals\Ens;
use App\Models\City;
use Illuminate\Support\Facades\Redis;
use App\Models\Country;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

class UserOpenController extends Controller
{
    /**
     * 发送验证码
     *
     * @param Request $request
     * @return void
     */
    public function sendCode(Request $request)
    {
        $toEmail             = $request->input('email');

        // 邮箱正则验证
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL) || !$toEmail) {
            return $this->error('The email address is invalid');
        }
        // 生成随机验证码
        $code = mt_rand(111111, 999999);
        // resdis
        Redis::setex($toEmail, 600, $code);

        $conetent = 'Your verification code: 【' . $code . '】and will expire in 10 minutes';

        Mail::raw($conetent, function ($message) use ($toEmail) {

            $message->to($toEmail)->subject("dome verification code");
        });

        return $this->success(null, 'The verification code is sent successfully. Please check your email address');
    }

    /**
     * 用户注册
     *
     * @param Request $request
     * @return void
     */
    public function sign(Request $request)
    {
        $email             = $request->input('email');
        $password          = $request->input('password');
        $invite            = $request->input('invite');
        $nickname          = $request->input('nickname');
        $code              = $request->input('code');
        $relation          = '';
        $ip                =  $request->getClientIp();

        $model              = new User();

        // 邮箱正则验证
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$email) {
            return $this->error('The email address is invalid');
        } else {
            // 邮箱唯一性验证
            $user = $model->where('email', $email)->count();
            if ($user) {
                return $this->error('The email address has been registered');
            }
        }

        // 参数校验
        if (!$password) {
            if (!$code) {
                return $this->error('Please fill in your password or verification code');
            } else {
                // 校验验证码
                $verify = Redis::get($email);
                if (!$verify || $code != $verify) {
                    return $this->error('Verification code error');
                }
            }
        }
        if (!$nickname) {
            $nickname = explode("@", $email)[0];
        }
        // 邀请码校验
        if ($invite) {
            // 关系链获取
            $parentID = id_random_code($invite);
            if ($parentID) {
                $parent =  $model->find($parentID);
                if (!$parent) {
                    return $this->error('Invalid invitation code');
                }
                $relation = $parent->relation . '-' . $parent->id;
            } else {
                return $this->error('Invalid invitation code');
            }
        }
        // 开始事务
        DB::beginTransaction();
        try {
            // 生成用户信息
            $user = $model->create([
                'email'               => $email,
                'password'            => Hash::make($password),
                'parent_invite'       => $invite,
                'relation'            => $relation,
                'register_ip'         => $ip,
                'nickname'            => $nickname
            ]);

            // 邀请码补全
            $invitationCode =  id_random_code($user->id, 8);

            $model->where('id', $user->id)->update([
                'invitation_code' => $invitationCode,
            ]);

            DB::commit();
        } catch (QueryException $ex) {
            DB::rollback();
            return $this->error('fail to register');
        }
        $data = [
            'id' => $user->id,
            'time' => time(),
            'sid' => 1,

        ];
        // 生成token
        $token = Ens::encrypt(base64_encode(json_encode($data)));

        $resultData = [
            "token" => $token,
            "id"    => $user->id,
        ];

        // 删除缓存验证码
        $verify = Redis::del($email);

        return $this->success($resultData, '');
    }

    /**
     * 用户登录
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        $email             = $request->input('email');
        $password          = $request->input('password');
        $code              = (int) $request->input('code');
        $ip                =  $request->getClientIp();

        // 用户校验
        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->error('Unregistered email address');
        }
        $count = $user->singleid;
        $count++;

        // 用户状态校验
        if ($user->status != 1) {
            return $this->error('The account status is abnormal');
        }
        // 密码验证码校验
        if ($password) {
            if (!Hash::check($password, $user->password)) {
                return $this->error('passwordWrong');
            }
        } else {
            $verify = Redis::get($email);

            if (!$verify || $code != $verify) {
                return $this->error('Verification code error');
            }
        }
        // 登录次数+1
        User::where('email', $email)->update([
            'last_ip'     => $ip,
            'singleid'    => $count
        ]);

        // 生成token
        $data = [
            'id' => $user->id,
            'time' => time(),
            'sid' => $count,

        ];
        // 生成token
        $token = Ens::encrypt(base64_encode(json_encode($data)));
        $resultData = [
            "token" => $token,
            "id"    => $user->id,
        ];

        $verify = Redis::del($email);


        return $this->success($resultData, '');
    }

    /**
     * 忘记密码
     *
     * @param Request $request
     * @return void
     */
    public function forgot(Request $request)
    {
        $email             = $request->input('email');
        $password          = $request->input('password');
        $code              = (int) $request->input('code');

        // 用户校验
        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->error('Unregistered email address');
        }
        $count = $user->singleid;
        $count++;
        // 参数校验
        if (!$password) {
            return $this->error('Please fill in your new password');
        }
        // 验证码校验
        $verify = Redis::get($email);
        if ($code != $verify) {
            return $this->error('Verification code error');
        } else { }

        // 更新数据
        $user->password = Hash::make($password);

        $user->save();

        $data = [
            'id' => $user->id,
            'time' => time(),
            'sid' => $count,

        ];
        // 生成token
        $token = Ens::encrypt(base64_encode(json_encode($data)));

        $resultData = [
            "token" => $token,
            "id"    => $user->id,
        ];

        $verify = Redis::del($email);

        return $this->success($resultData, '');
    }

    /**
     * 获取国家列表
     *
     * @param Request $request
     * @return void
     */
    public function getCountries(Request $request)
    {
        $page         = (int) $request->input('page');
        $limit        = (int) $request->input('limit');

        if ($limit) {
            $countries = Country::offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();
        } else {
            $countries = Country::all();
        }


        return $this->success($countries->toArray(), '');
    }

    /**
     * 获取城市列表
     *
     * @param Request $request
     * @return void
     */
    public function getCities(Request $request, $cn)
    {
        $page         = (int) $request->input('page');
        $limit        = (int) $request->input('limit');
        $cities = City::where('country_id', $cn);

        if ($limit) {
            $cities->offset(($page - 1) * $limit)
                ->limit($limit);
        }

        return $this->success($cities->get()->toArray(), '');
    }

    /**
     * 获取语言列表
     *
     * @param Request $request
     * @return void
     */
    public function getLanguages(Request $request)
    {
        $langs = Language::all()->toArray();

        return $this->success($langs, '');
    }
}
