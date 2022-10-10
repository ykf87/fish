<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PayRequest extends BaseRequest
{
    public function rulePay()
    {
        return [
            'course_id' => 'required|int',
            'payment_type' => ['required', Rule::in(['paypal'])],
        ];
    }

}
