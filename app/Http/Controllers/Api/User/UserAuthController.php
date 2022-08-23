<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Globals\Ens;
use Encore\Admin\Grid\Displayers\Limit;

class UserAuthController extends Controller
{
    /**
     * 获取用户信息
     *
     * @param Request $request
     * @return void
     */
    public function GetUserInfo(Request $request)
    {
        $user         = $request->get('_user');

        $info = User::find($user->id)->toArray();

        var_dump($info);

        return $this->success($info, '');
    }

    /**
     * 领样表
     *
     * @param Request $request
     * @return void
     */
    public function samples(Request $request)
    {
        $user         = $request->get('_user');
        $state        = $request->input('state');
        $page         = (int) $request->input('page', 1);
        $limit        = (int) $request->input('limit', 20);

        $samples = Sample::where("user_id", $user->id)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->toArray();
        return $this->success($samples, '');
    }

    /**
     * 取消领样
     *
     * @param Request $request
     * @return void
     */
    public function cancelSample(Request $request)
    {
        $user         = $request->get('_user');
        $id         = (int) $request->input('id');

        $samples = Sample::where("user_id", $user->id)
            ->where('id', $id)
            ->update([
                'state'
            ]);
        return $this->success($samples, '');
    }

    /**
     * 账号管理列表
     *
     * @param Request $request
     * @return void
     */
    public function GetUserDarren(Request $request)
    {
        $user         = $request->get('_user');
        $search         = $request->input('search');

        $accounts = Sample::where("user_id", $user->id)
            ->where('name', $search)
            ->get();

        foreach ($accounts as $key => $value) {
            # code...
        }
        return $this->success($samples, '');
    }

    /**
     * 新增tk账号
     *
     * @param Request $request
     * @return void
     */
    public function AddDarren(Request $request)
    {
        $user         = $request->get('_user');
        $nickname         = $request->input('nickname');
        $account          = $request->input('account');
        $backend          = $request->input('backend');
        $fans             = $request->input('fans');
        $praise_nums      = $request->input('praise_nums');


        $accounts = Sample::where("user_id", $user->id)
            ->where('name', $search)
            ->get();

        foreach ($accounts as $key => $value) {
            # code...
        }
        return $this->success($samples, '');
    }

    /**
     * 商品收藏与取消收藏
     *
     * @param Request $request
     * @return void
     */
    public function productCollectionAndCancel(Request $request)
    {
        $user         = $request->get('_user');
        $id           = $request->input('id');
    }

    /**
     * 商品收藏列表
     *
     * @param Request $request
     * @return void
     */
    public function productCollectionList(Request $request)
    {
        $user         = $request->get('_user');
        $id         = $request->input('id');

        $conllections;
    }
}
