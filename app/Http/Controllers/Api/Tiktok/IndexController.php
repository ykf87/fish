<?php

namespace App\Http\Controllers\Api\Tiktok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller{
	public function index(Request $request){
		$days 		= $request->input('days');
		$state 		= $request->input('state');
		$account_id	= $request->input('account_id');
		$order_page	= $request->input('order_page');
		$order_limit= $request->input('order_limit');

		$arr 		= [
			'fund_data'		=> [
				'total_sales'			=> '',
				'effective_sales'		=> '',
				'full_commission'		=> '',
				'effective_commission'	=> '',
				'all_singular'			=> '',
				'effective_singular'	=> 0,
			],
		]; 
	}
}
