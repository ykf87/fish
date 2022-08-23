<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Globals\Ens;
use App\Models\User;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->get('_user')) {
            return $next($request);
        }
        $token      = $request->header('token') or $request->cookie('token') or $request->input('token');
        if ($token) {
            $info       = base64_decode(Ens::decrypt($token));
            if ($info) {
                $info   = json_decode($info, true);
                if (isset($info['id']) && isset($info['time']) && isset($info['sid'])) {
                    if (time() - $info['time'] < (86400 * 60)) { //60天后 token 失效
                        $user   = User::find($info['id']);
                        var_dump($info);
                        if ($user && $user->singleid == $info['sid']) {
                            $request->merge(['_user' => $user]);
                            return $next($request);
                        }
                    }
                }
            }
        }
        return response()->json(['code' => 401, 'msg' => __('Please Login'), 'data' => null]);
    }
}
