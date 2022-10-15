<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductVideoRequest;
use App\services\ProductVideoService;

class ProductVideoController extends Controller
{
    protected $videoService;
    protected $uid;

    public function __construct(ProductVideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    //视频领取历史记录
    public function receivedList(ProductVideoRequest $request)
    {
        $user = $request->get('_user');
        $param = $request->all();
        $param['uid'] = $user->id;
        $data = $this->videoService->receivedList($param);

        return $this->success($data);
    }

    //查询商品的视频领取情况
    public function check(ProductVideoRequest $request)
    {
        $user = $request->get('_user');
        $product = $this->videoService->getProduct($request->input('pid'));
        if (!$product) {
            return $this->error('');
        }
        $data = $this->videoService->check($product, $user->id);

        return $this->success($data);
    }

    //商品原始视频列表
    public function original(ProductVideoRequest $request)
    {
        $user = $request->get('_user');
        $product = $this->videoService->getProduct($request->input('pid'));
        if (!$product) {
            return $this->error('The product is not exist');
        }
        $data = $this->videoService->originalVideo($product, $user->id);

        return $this->success($data);
    }

    //领取原始视频
    public function receiveOriginal(ProductVideoRequest $request)
    {
        $user = $request->get('_user');
        $pid = $request->input('pid');
        $vid = $request->input('vid');

        $product = $this->videoService->getProduct($pid);
        if (!$product) {
            return $this->error('The product is not exist');
        }

        $video = $this->videoService->getVideo($vid, $pid);
        if (!$video) {
            return $this->error('The video is not exist');
        }

        if (!$this->videoService->todayReceivedStatus($user->id,$pid, 'original')) {
            return $this->error('The quantity received today has reached the upper limit');
        }

        $this->videoService->receiveVideo($user->id, $product, $video);

        $data = [
            'id' => $video->id,
            'title' => $video->title,
            'video_url' => $video->video_url,
            'full_video_url' => $video->full_video_url,
        ];

        return $this->success($data);
    }

    //领取剪辑视频
    public function receiveClip(ProductVideoRequest $request)
    {
        $user = $request->get('_user');
        $pid = $request->input('pid');
        $vid = $request->input('vid');

        $product = $this->videoService->getProduct($pid);
        if (!$product) {
            return $this->error('The product is not exist');
        }

        if (!$this->videoService->todayReceivedStatus($user->id,$pid, 'clip')) {
            return $this->error('The quantity received today has reached the upper limit');
        }

        if (!empty($vid)) { //如果vid不为空，那么认为是再次下载
            $video = $this->videoService->getVideo($vid, $pid);
            if (!$video) {
                return $this->error('The video is not exist');
            }
            if (!$this->videoService->checkReceiveById($user->id, $video->id)) { //判断视频是否可以被本人领取
                return $this->error('The video is received by others');
            }
        } else {
            $video = $this->videoService->getOneVideo($pid); //随机获取一个剪辑视频
            if (!$video) {
                return $this->error('Video library is empty');
            }
        }

        $this->videoService->receiveVideo($user->id, $product, $video);

        $data = [
            'id' => $video->id,
            'title' => $video->title,
            'video_url' => $video->video_url,
            'full_video_url' => $video->full_video_url,
        ];

        return $this->success($data);
    }

    public function getProduct($pid)
    {
        $product = $this->videoService->getProduct($pid);
        if (!$product) {
            return $this->error('');
        }
        return $product;
    }
}
