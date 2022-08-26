<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	public function success($data, $msg = '')
	{
		if (!$msg) {
			$msg 	= 'Success';
		}
		return response()->json([
			'code' => 200,
			'msg' => __($msg, [], \Request::header('langs') ? \Request::header('langs') : 'zh-CN'),
			'data' => $data,
		]);
	}
	public function error($msg = '', $data = null)
	{
		if (!$msg) {
			$msg 	= 'Error';
		}
		return response()->json([
			'code' => 500,
			'msg' => __('api.tips.' . $msg, [], \Request::header('langs') ? \Request::header('langs') : 'zh-CN'),
			'data' => $data,
		]);
	}
}
