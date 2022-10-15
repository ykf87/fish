<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\DictRequest;
use App\services\DictService;

class DictController extends Controller
{
    //获取字典信息
    public function info(DictRequest $request, DictService $dictService)
    {
        $param = $request->all();

        $info = $dictService->info($param);

        if ($info) {
            return $this->success($info);
        } else {
            return $this->error('Dict not exist');
        }
    }
}
