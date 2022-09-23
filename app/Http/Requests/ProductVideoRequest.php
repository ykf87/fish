<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ProductVideoRequest extends BaseRequest
{
    public function ruleCheck()
    {
        return [
            'pid' => 'required|int',
        ];

    }

    public function ruleReceiveClip()
    {
        return [
            'pid' => 'required|int',
            'vid' => 'int',
        ];

    }

    public function ruleReceiveOriginal()
    {
        return [
            'pid' => 'required|int',
            'vid' => 'required|int',
        ];

    }
}
