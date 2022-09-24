<?php

namespace App\services;

use App\Models\TiktokProduct;
use App\Models\TiktokProductsVideo;
use App\Models\TiktokVideoReceive;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductVideoService
{
    public function check(TiktokProduct $product, $uid)
    {
        $clip_video_num = TiktokProductsVideo::where('pid', $product->id)
            ->where('type', 'clip')
            ->where('receive_status', 0)
            ->count();

        $data = [
            'original_video_num' => $product->original_video_num,
            'clip_video_num' => $clip_video_num,
            'received_original_num' => self::countReceived($uid, $product->id, 'original'),
            'received_clip_num' => self::countReceived($uid, $product->id, 'clip'),
            'today_original_status' => self::todayReceivedStatus($uid, $product->id, 'original'),
            'today_clip_status' => self::todayReceivedStatus($uid, $product->id, 'clip'),
        ];

        return $data;
    }

    public function receivedList($param)
    {
        if (empty($param['page'])) $param['page'] = 1;
        if (empty($param['limit'])) $param['limit'] = 1;

        $query = TiktokVideoReceive::query()->where('uid', $param['uid'])
            ->select('pid', 'vid', 'type', 'receive_time');

        if (!empty($param['pid'])) {
            $query->where('pid', $param['pid']);
        }
        if (!empty($param['type'])) {
            $query->where('type', $param['type']);
        }

        $total = $query->count();
        $query->with('product:id,name')->with('video:id,title,video_url');

        $res = $query->orderByDesc('id')->offset(($param['page'] - 1) * $param['limit'])->limit($param['limit'])->get();

        return [
            'page'			=> $param['page'],
            'limit'			=> $param['limit'],
            'total_limit'	=> $total,
            'lists'	=> $res,
        ];
    }

    public function originalVideo(TiktokProduct $product, $uid)
    {
        $res = TiktokProductsVideo::where('pid', $product->id)
            ->where('type', 'original')
            ->select('id', 'title', 'video_url', 'created_at')
            ->get()
            ->toArray();

        $vids = Arr::pluck($res, 'id');

        $received = TiktokVideoReceive::whereIn('vid', $vids)
            ->where('uid', $uid)
            ->select('receive_time', 'vid')
            ->get()
            ->toArray();
        $received = array_column($received, 'receive_time', 'vid');

        foreach ($res as &$info) {
            if (isset($received[$info['id']])) {
                $info['is_received'] = 1;
                $info['received_time'] =$received[$info['id']];
            } else {
                $info['is_received'] = 0;
                $info['received_time'] = '-';
            }
        }

        return $res;
    }

    /**
     * 某类型视频今日领取状态（是否还可领取）
     * @param int $uid 用户id
     * @param int $pid 商品id
     * @param string $type 视频类型
     * @return int
     */
    public function todayReceivedStatus($uid, $pid, $type)
    {
        return self::countReceived($uid, $pid, $type, 'today') >= config('tiktok.video_receive.day_limit_' . $type) ? 0 : 1;
    }

    //判断该视频是否可以被领取
    public function checkReceiveById($uid, $vid)
    {
        $has_received =  TiktokVideoReceive::select('uid')->where('vid', $vid)->first();
        if (!$has_received) return true; // 未被任何人领取，可领取
        if ($has_received->uid == $uid) {
            return true;
        } else {
            return false;
        }
    }

    //领取视频
    public function receiveVideo($uid, $product, $video)
    {
        //如果是原始视频，每个人都可以领取一次，如果是剪辑视频，只能被一个人领取
        if ($video->type == 'clip') {
            $has_received =  TiktokVideoReceive::where('vid', $video->id)->count();
        } else {
            $has_received =  TiktokVideoReceive::where('vid', $video->id)->where('uid', $uid)->count();
        }

        if (!$has_received) {
            DB::transaction(function () use ($product, $video, $uid) {
                TiktokVideoReceive::create([
                    'uid' => $uid,
                    'pid' => $product->id,
                    'vid' => $video->id,
                    'type' => $video->type,
                    'receive_time' => time()
                ]);

                $video->receive_status = 1;
                $video->save();

            });

        }
    }


    /**
     * 统计用户视频领取数量
     * @param $uid
     * @param $pid
     * @param string $type 视频类型
     * @param string $scope today：查询当天的领取数量 all：查询领取总数
     * @return int
     */
    public function countReceived($uid, $pid = 0, $type = 'original', $scope = 'all')
    {
        $query = TiktokVideoReceive::where('uid', $uid)
            ->where('type', $type);
        if (!empty($pid)) {
            $query->where('pid', $pid);
        }
        if ($scope == 'today') {
            $query->where('receive_time', '>=', strtotime(date('Y-m-d 00:00:00')));
        }

        return $query->count();
    }

    public function getProduct($pid)
    {
        return TiktokProduct::find($pid);
    }

    public function getOneVideo($pid)
    {
        $res = TiktokProductsVideo::where('pid', $pid)->where('receive_status', 0)->first();
        return $res;
    }

    public function getVideo($vid, $pid = 0)
    {
        $res = TiktokProductsVideo::where('id', $vid);
        if (!empty($pid)) $res->where('pid', $pid);
        return $res->first();
    }

}