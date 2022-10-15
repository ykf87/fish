<?php

namespace App\Http\Requests;

class DictRequest extends BaseRequest
{
    public function ruleInfo()
    {
        return [
            'dict_key' => 'required',
        ];
    }

}
