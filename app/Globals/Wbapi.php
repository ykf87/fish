<?php
namespace App\Globals;
use GuzzleHttp;
/**
 * 请求ws封装
 */

class WbApi{
    /**
     * 发送指令到ws服务器,下发到设备执行操作
     * @param $uid          int     用户id号
     * @param $deviceId     string  设备id号列表,用逗号分隔
     * @param $type         string  执行的操作,和api接口方约定好的, exit - 退出设备
     * @param $data         array   任务执行参数
     * data 参数有要求
     *      如果是退出设备, data 参数为空
     *      如果执行任务, 一维必须有的键, type, code, msg, data
     *      内部 data 必须有 file, quality, id, req_time, config 键
     */
    public static function Send($uid, $deviceId = '', $data = null, $type = 'send'){
        try {
            // $aarr   = ['type'=>'doiyinyanghao', 'data'=>['config' => ['dianzan'=> 0.2,'seetime'=> [3, 20], 'comments_probability' => 0.1, 'comments' => ['不错哦','喜欢这个视频','怎么拍的?', '挺好的'], 'videos' => [10, 50]], 'quality'=>2, 'file' => 'robot.douyin', 'id' => 1, 'req_time' => time()], 'code' => 200, 'msg' => '', 'noreback' => false];
            // $aarr    = ['type'=>'stop', 'data'=>[1], 'code' => 200, 'msg' => '', 'noreback' => false];
            if($data && !is_string($data)){
                $data   = json_encode($data);
            }
            if(is_array($deviceId)){
                $deviceId   = implode(',', $deviceId);
            }
            // print_r($data);
            // print_r($deviceId);
            $http = new GuzzleHttp\Client;
            $response = $http->post(env('TASK_API_URL'), [
                'form_params' => [
                    'id'    => $uid,
                    'did'   => $deviceId,
                    'type'  => $type,
                    'data'  => $data,
                ],
            ]);
            $res    = (string)$response->getBody();
            return $res;
        } catch (\Exception $e) {
            // echo $e->getMessage();
            return false;
        }
    }
    /**
     * 配置
     */
    private static function conf(){
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            // replace the value below with a key of your own!
            InMemory::base64Encoded('mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=')
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );
    }

    /**
     * 加密
     */
    public static function encrypt($id, $time){

    }

    /**
     * 解密
     */
    public static function decrypt($token){

    }
}
