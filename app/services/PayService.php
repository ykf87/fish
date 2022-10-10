<?php

namespace App\services;

use App\Helper\Paypal;
use App\Models\CourseOrder;

class PayService
{
    public function pay($param)
    {
        $result = ['success' => 'false'];
        switch ($param['payment_type']) {
            case 'paypal':
                $pay = new Paypal();
                $result = $pay->pay($param);

                break;
            default :
                break;
        }

        if ($result['success']) {
            if ($param['product_type'] == 'course') {
                $param['pay_id'] = $result['pay_id'];
                $param['total'] = $param['price'];
                $param['currency'] = 'USD';
                $param['addtime'] = time();
                CourseOrder::create($param);
            }
        }

        return $result;
    }

    public function success($param)
    {
        $result = [
            'success' => 'false',
            'msg' => ''
        ];
        if ($param['product_type'] == 'course') {

            $order = CourseOrder::where('pay_id', $param['paymentId'])->first();
            if ($order['total'] !== $param['total']) {
                $result['msg'] = 'order price error';
            }

            if ($order['status'] == 1) {
                $order->status = 20;
                $order->paytime = time();
                $order->payer_info = $param['payer_info'];
                $order->save();
                $result['success'] = true;
            }
        }

        return $result;
    }

}