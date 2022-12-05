<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

/**
 * 接口签名
 *
 */
class VerifyApiSign
{
    // 忽略列表
    protected $except = [
        'api/pay/callback/course',
        'api/pay/pay/course',
        'tiktok/callback',
        'tiktok/proinfo',
        'tiktok/orderinfo',
        'tiktok/aggregate',
    ];

    // 时间误差
    protected $timeError = 30;

    // 错误信息
    protected $error = '';

    // 密钥
    protected $secretKey = '';

    // 签名字段
    protected $signField = 'sign';

    // 时间字段
    protected $timeField = 'timestamp';

    //是否启用
    protected $switch = false;

    public function __construct()
    {
         $this->secretKey = env('APP_SIGN_KEY', 'ab*#789');
         if (env('APP_SIGN_SWITCH') == true) {
            $this->switch = true;
         }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
        if (!$this->switch || $this->inExceptArray($request) || ($this->signMatch($request) && $this->allowTimestamp($request))) {
            return $next($request);
        }

        $msg = 'Api sign error';
        if (in_array(env('APP_ENV'), ['local', 'dev'])) {
            $msg.= ' message: ' . $this->error;
        }

        return response()->json([
            'code' =>   500,
            'msg' => __('api.error_tips.' . $msg, [], $request->header('lang') ? $request->header('lang') : 'en'),
            'data' =>   [],
        ]);
    }

    /**
     * 判断当前请求是否在忽略列表中
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断用户请求是否在对应时间范围
     */
    protected function allowTimestamp($request)
    {
        $queryTime = Carbon::createFromTimestamp($request->header($this->timeField, 0));
        $lfTime = Carbon::now()->subSeconds($this->timeError);
        $rfTime = Carbon::now()->addSeconds($this->timeError);
        if ($queryTime->between($lfTime, $rfTime, true)) {
            return true;
        }
        $this->error = 'timestamp error';
        return false;
    }

    /**
     * 签名验证
     */
    protected function signMatch(Request $request)
    {
        if ($request->method() == 'PUT') {
            $data = $request->getContent();
            $data = json_decode($data, true);
        } else {
            $data = $request->all();
        }

        $data[$this->timeField] = $request->header($this->timeField);

        // 移除sign字段
        if (isset($data['sign'])) {
            unset($data['sign']);
        }

        ksort($data);
        $sign = '';
        foreach ($data as $k => $v) {
            if ($this->signField !== $k) {
                $sign .= $k . $v;
            }
        }

        $sign_str = $sign . $this->secretKey;
        $md5_sign_str = strtoupper(md5($sign_str));

        if ($md5_sign_str === $request->header($this->signField, null)) {
            return true;
        }
        $this->error = sprintf("sign_str=%s,md5_value=%s", $sign_str, $md5_sign_str);
        return false;
    }
}
