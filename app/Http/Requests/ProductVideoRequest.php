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

    public function ruleOriginal()
    {
        return [
            'pid' => 'required|int',
        ];
    }

    public function ruleReceivedList()
    {
        return [
            'pid' => 'int',
            'type' => [Rule::in(['original', 'clip'])],
            'page' => 'int|min:1',
            'limit' => 'int|min:1|max:50',
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
