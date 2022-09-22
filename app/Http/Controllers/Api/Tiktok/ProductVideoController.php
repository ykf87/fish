<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use App\services\ProductVideoService;
use Illuminate\Http\Request;

class ProductVideoController extends Controller
{
    protected $videoService;

    public function __construct(ProductVideoService $videoService)
    {
        $this->videoService = $videoService;

    }

    public function check(Request $request)
    {
        $user = $request->get('_user');
        print_r($user);
    }
}
