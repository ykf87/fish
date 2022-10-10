<?php

namespace App\Http\Controllers\Api\Pay;

use App\Helper\Paypal;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayRequest;
use App\services\CourseService;
use App\services\PayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
    //课程支付
    public function payCourse(PayRequest $request, CourseService $courseService, PayService $payService)
    {
        $course_id = $request->input('course_id');
        $course = $courseService->info($course_id);

        if (!$course) {
            return $this->error('', 'the course is not exist');
        }

        $user = $request->get('_user');

        $param = [
            'payment_type' => $request->input('payment_type'),
            'product_type' => 'course',
            'product' => $course['title'],
            'price' => $course['price'],
            'description' => $request->input('description'),
            'uid' => $user->id,
            'course_id' => $course_id,
        ];

        $result = $payService->pay($param);

        if ($result['success']) {
            return $this->success($result);
        } else {
            return $this->error($result);
        }

    }

    //课程支付回调
    public function callbackCourse(Request $request, Paypal $paypal, PayService $payService)
    {
        $param = $request->all();
        $rtn = ['success' => true];
        if ($param['success'] == 'false' && !isset($param['paymentId']) && !isset($param['PayerID'])) {
            $rtn['msg'] = 'Cancel payment';
        }
        if (!isset($param['success'], $param['paymentId'], $param['PayerID'])) {
            $rtn['msg'] = 'Payment failed';
        }
        if ((bool)$_GET['success'] === 'false') {
            $rtn['msg'] = sprintf("Payment failed, paymentId=%s, PayerID=%", $param['paymentId'], $param['PayerID']);
        }

        if (!empty($rtn['msg'])) $rtn['success'] = false;

        if ($rtn['success']) {
            $rtn = $paypal->callback($param);
        }

        if ($rtn['success']) {
            $rtn['product_type'] = 'course';
            $rtn['paymentId'] = $param['paymentId'];
            $payService->success($rtn);
            return $this->success($rtn);
        } else {
            return $this->error('', $rtn['msg']);
        }
    }

    //支付异步回调
    public function notify(Request $request)
    {
        $json = file_get_contents('php://input');
        $ip = $request->ip();
        $data = $ip . ' ' . $json;
        Log::channel('pay')->debug($data);
    }

}
