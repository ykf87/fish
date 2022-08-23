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
        $flag = Mail::raw($conetent, function ($message) use ($toEmail) {
            $message->to($toEmail)->subject("dome verification code");
        });
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

        // 邮箱正则验证
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$email) {
            return $this->error('The email address is invalid');
        } else {
            // 邮箱唯一性验证
            $user = User::where('email', $email)->count();
            if ($user) {
                return $this->error('The email address has been registered');
            }
        }

        // 参数校验
        if (!$password) {
            return $this->error('Please fill in your password');
        }
        if (!$nickname) {
            $nickname = explode("@", $email)[0];
        }
        // 邮箱验证码验证

        // 生成用户信息
        $user = User::create([
            'email'        => $email,
            'password'     => Hash::make($password),
            'invite'       => $invite,
            '分销链',
            'login_acount' => 1,
            'name'         => $nickname
        ]);

        // 生成token
        $token = base64_encode(Ens::encrypt('{"id": "' . $user->id . '","time":' . time() . '}'));

        $resultData = [
            "token" => $token,
            "id"    => $user->id,
        ];

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
        $redis             = new Redis;

        // 用户校验
        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->error('Unregistered email address');
        }
        // 密码验证码校验
        if ($password) {
            if (!Hash::check($password, $user->password)) {
                return $this->error('passwordWrong');
            }
        } else {
            $verify = $redis->get($email);
            if ($code != $verify) {
                return $this->error('Verification code error');
            } else {
                $verify = $redis->del($email);
            }
        }

        // 生成token
        $token = base64_encode(Ens::encrypt('{"id": "' . $user->id . '","time":' . time() . '}'));

        $resultData = [
            "token" => $token,
            "id"    => $user->id,
        ];


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
        $redis             = new Redis;

        // 用户校验
        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->error('Unregistered email address');
        }
        // 参数校验
        if (!$password) {
            return $this->error('Please fill in your new password');
        }
        // 验证码校验
        $verify = $redis->get($email);
        if ($code != $verify) {
            return $this->error('Verification code error');
        } else {
            $verify = $redis->del($email);
        }

        // 更新数据
        $user->password = Hash::make($password);

        $user->save();

        // 生成token
        $token = Ens::encrypt(base64_encode('{"id": "' . $user->id . '","time":' . time() . '}'));

        $resultData = [
            "token" => $token,
        ];

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
        $limit        = (int) $request->input('limit', 20);

        $countries = Country::offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return $this->success($countries, '');
    }

    /**
     * 获取城市列表
     *
     * @param Request $request
     * @return void
     */
    public function getCities(Request $request, $cn)
    {
        echo $cn;
        $page         = (int) $request->input('page', 1);
        $limit        = (int) $request->input('limit', 20);
        $cities = City::where('country_id', $cn)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return $this->success($cities, '');
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
