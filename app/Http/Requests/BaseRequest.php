<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * 定义验证规则
     *
     * @return array
     */
    public function rules()
    {
        $rule_action = 'rule' . ucfirst($this->route()->getActionMethod());

        if (method_exists($this, $rule_action))
            return $this->$rule_action();

        return $this->getDefaultRules();
    }


    /**
     * 默认 验证规则
     * @return array
     */
    protected function getDefaultRules()
    {
        return [];
    }

    /**
     * 验证消息通过json抛出（api开发时用到）
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 500,
            'msg' => __('api.error_tips.' . $validator->errors()->first(), [], \Request::header('lang') ? \Request::header('lang') : 'en'),
        ]));
    }
}