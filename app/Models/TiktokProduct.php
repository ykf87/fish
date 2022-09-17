<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Facades\Admin;
use App\Models\TiktokProductsSku;
use App\Models\TiktokProductsResion;
use App\Models\TiktokShop;
use App\Models\TiktokAccount;
use App\Models\TiktokCategory;
use App\Models\TiktokProductsCategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TiktokProduct extends Model
{
	use HasFactory;
	public $timestamps = false;
	public static $status 	= [
		1	=> '草稿',
		2	=> '待定',
		3	=> '失败',
		4	=> '上线',
		5	=> '卖家停用',
		6	=> '平台停用',
		7	=> '冻结',
		8	=> '删除',
        9	=> '下架',
	];
	public static $statusLabel 	= [
		1	=> 'default',
		2	=> 'primary',
		3	=> 'info',
		4	=> 'success',
		5	=> 'warning',
		6	=> 'warning',
		7	=> 'danger',
		8	=> 'danger',
        9	=> 'danger',
	];

	protected $appends = ['left_icon'];

	//更新TK产品
	public static function updFromTiktok($row)
	{
		if (!isset($row['product_id'])) {
			return false;
		}
		$productid 		= $row['product_id'];
		$product 		= self::where('pid', $productid)->first();
		if (!$product) {
			return false;
		}

		if (!isset($row['category_list']) || !isset($row['images'])) {
			return false;
		}

		DB::transaction(function () use ($row, $product) {
			$product->name 			= $row['product_name'];
			$product->status 		= $row['product_status'];
			$product->description 	= $row['description'];
			if (isset($row['brand'])) {
				$product->brand 	= $row['brand'];
			}

			$images 				= [];
			$thums 					= [];
			foreach ($row['images'] as $item) {
				$images[] 			= $item['url_list'][0];
				$thums[]			= $item['thumb_url_list'][0];
			}
			// $images 				= array_flip(array_flip($images));
			// $thums 					= array_flip(array_flip($thums));
			$product->thumbs 		= implode(',', $thums);
			$product->images 		= implode(',', $images);

			$cates 				= [];
			$proCateIds 		= [];
			foreach ($row['category_list'] as $item) {
				$cates[$item['id']] 		= [
					'id'		=> $item['id'],
					'parent'	=> $item['parent_id'],
					'name'		=> $item['local_display_name'],
					'is_leaf'	=> (int) $item['is_leaf'],
				];
				$proCateIds[]	= ['id' => $product->id, 'cateid' => $item['id']];
			}
			$getCateIds 		= array_keys($cates);
			$hads 				= TiktokCategory::whereIn('id', $getCateIds)->pluck('id', 'id')->toArray();
			$cateInsert 		= array_diff_key($cates, $hads);
			if (count($cateInsert) > 0) {
				TiktokCategory::insert($cateInsert);
			}
			if (count($proCateIds) > 0) {
				TiktokProductsCategory::where('id', $product->id)->delete();
				TiktokProductsCategory::insert($proCateIds);
			}

			$curr 				= '';
			$minprice 			= 9999999999;
			$maxprice			= 0;
			$stock 				= 0;
			if (isset($row['skus'])) {
				foreach ($row['skus'] as $item) {

					$original_price = isset($item['price']['original_price']) ? $item['price']['original_price'] : 0;

					if ($minprice > $original_price) {
						$minprice 	= $original_price;
					}
					if ($maxprice < $original_price) {
						$maxprice 	= $original_price;
					}
					$curr 		= $item['price']['currency'];
					$stock 		+= $item['stock_infos'][0]['available_stock'];

					$insertSkus[$item['id']]	= [
						'pid'				=> $product->id,
						'sid'				=> $item['id'],
						'currency'			=> $item['price']['currency'],
						'original_price'	=> $item['price']['original_price'],
						'price_include_vat'	=> isset($item['price']['price_include_vat']) ? $item['price']['price_include_vat'] : 0,
						'seller_sku'		=> $item['seller_sku'],
						'stock'				=> $item['stock_infos'][0]['available_stock'],
					];
				}
				$hads 			= TiktokProductsSku::where('pid', $product->id)->pluck('pid', 'sid')->toArray();
				$insertSkusNew 	= array_diff_key($insertSkus, $hads);
				$delSkus 		= array_diff_key($hads, $insertSkus);
				if (count($delSkus) > 0) {
					TiktokProductsSku::where('pid', $product->id)->whereIn('sid', array_keys($delSkus))->delete();
				}
				if (count($insertSkusNew) > 0) {
					TiktokProductsSku::insert($insertSkusNew);
				}
				$product->currency 	= $curr;
				if ($maxprice >= $minprice) {
					$product->maxprice 	= $maxprice;
					$product->minprice 	= $minprice;
				}
				$product->stocks 	= $stock;
			}

			$product->save();
		});
	}

	//新增TK产品
	public static function addFromTiktok($products, $shopid, $accountid)
	{
		$dbproids 		= self::where('shop_id', $shopid)->pluck('id', 'pid')->toArray();
		$getproids 		= Arr::pluck($products, 'id', 'id');
		$adminId 		= Admin::user()->id;

		$inserts 		= [];
		$insertSkus 	= [];
		$sellresions 	= [];
		$pids 			= [];
		foreach ($products as $item) {
			if (isset($dbproids[$item['id']])) {
				continue;
			}
			$pids[] 	= $item['id'];
			$minprice 	= 999999999;
			$maxprice 	= 0;
			$stock 		= 0;
			$curr 		= '';
			if (isset($item['skus'])) {
				foreach ($item['skus'] as $sitem) {

					$original_price = isset($sitem['price']['original_price']) ? $sitem['price']['original_price'] : 0;

					if ($minprice > $original_price) {
						$minprice 	= $original_price;
					}
					if ($maxprice < $original_price) {
						$maxprice 	= $original_price;
					}

					$curr 		= $sitem['price']['currency'];
					$stock 		+= $sitem['stock_infos'][0]['available_stock'];

					$insertSkus[$item['id']][]	= [
						'sid'				=> $sitem['id'],
						'currency'			=> $sitem['price']['currency'],
						'original_price'	=> $sitem['price']['original_price'],
						'price_include_vat'	=> isset($sitem['price']['price_include_vat']) ? $sitem['price']['price_include_vat'] : 0,
						'seller_sku'		=> $sitem['seller_sku'],
						'stock'				=> $sitem['stock_infos'][0]['available_stock'],
					];
				}
			} else {
				$minprice 	= 0;
			}
			if (isset($item['sale_regions'])) {
				foreach ($item['sale_regions'] as $rsion) {
					$sellresions[$item['id']][] 	= ['resion' => $rsion];
				}
			}
			if ($maxprice < $minprice) {
				$minprice 	= 0;
			}
			$arr 		= [
				'aid'			=> $adminId,
				'account_id'	=> $accountid,
				'shop_id'		=> $shopid,
				'pid'			=> $item['id'],
				'name'			=> $item['name'],
				'create_time'	=> $item['create_time'],
				'status'		=> $item['status'],
				'maxprice'		=> $maxprice,
				'minprice'		=> $minprice,
				'currency'		=> $curr,
				'stocks'		=> $stock,
			];
			$inserts[] 					= $arr;
			// self::insert($arr);
		}

		if (count($inserts) > 1) {
			self::insert($inserts);

			$res 	= self::whereIn('pid', $pids)->pluck('id', 'pid')->toArray();
			$isk 	= [];
			$isr 	= [];
			// print_r($insertSkus);
			// print_r($sellresions);
			foreach ($insertSkus as $pid => $arr) {
				if (isset($res[$pid])) {
					foreach ($arr as $bbb) {
						$bbb['pid']	= $res[$pid];
						$isk[] 		= $bbb;
					}
				}
			}
			if (count($isk) > 0) {
				TiktokProductsSku::insert($isk);
			}
			foreach ($sellresions as $pid => $vals) {
				if (isset($res[$pid])) {
					foreach ($vals as $bbb) {
						$bbb['pid']	= $res[$pid];
						$isr[] 		= $bbb;
					}
				}
			}
			if (count($isr) > 0) {
				TiktokProductsResion::insert($isr);
			}
		}


		TiktokShop::where('id', $shopid)->update(['product_number' => self::where('shop_id', $shopid)->count()]);
		TiktokAccount::where('id', $accountid)->update(['product_num' => self::where('account_id', $accountid)->count()]);
	}

	//前端查询
	public static function frontList(int $page, int $limit, $q, $cate, $sort, $is_samples = null, $cb = null, $region)
	{
		if ($page < 1) {
			$page 	= 1;
		}
		if ($limit < 1) {
			$limit 	= 20;
		}
		$obj 		= DB::table('tiktok_products as p')
            ->select(
                'p.id', 'p.pid', 'p.images as image', 'p.name as title', 'p.stocks as stock', 'p.sales as cumulative_sales',
                'p.minprice as unit_price', 'p.commission as commission_ratio', 'p.commission_price as commission', 'p.currency',
                'p.is_samples', 'p.status', 'shop.type as cb')
            ->distinct()->where('p.status', 4)->where('commission', '>', 0.01);
		$obj 		= $obj->leftJoin('tiktok_shops as shop', 'shop.id', '=', 'p.shop_id');
		if ($cb) {
			$obj 		= $obj->where('shop.type', $cb);
		}
		if ($q) {
			$obj 	= $obj->where('p.name', 'like', "%$q%");
		}
		if ($is_samples !== null) {
			$obj 	= $obj->where('p.is_samples', $is_samples);
		}
		if ($cate > 0) {
			$cateids 		= TiktokCategory::where('parent', $cate)->pluck('id')->toArray();
			$cateids[] 		= $cate;
			$cateids 		= array_flip(array_flip($cateids));

			$obj 			= $obj->rightJoin('tiktok_products_categories as c', 'c.id', '=', 'p.id')->whereIn('c.cateid', $cateids);
		}
		switch ($sort) {
			case 1: //到手价升序
				$obj 		= $obj->orderBy('p.minprice');
				break;
			case 2: //到手价降序
				$obj 		= $obj->orderByDesc('p.minprice');
				break;
			case 3: //佣金比例升序
				$obj 		= $obj->orderBy('p.commission');
				break;
			case 4: //佣金比例降序
				$obj 		= $obj->orderByDesc('p.commission');
				break;
			case 5: //佣金金额升序
				$obj 		= $obj->orderBy('p.commission_price');
				break;
			case 6: //佣金金额降序
				$obj 		= $obj->orderByDesc('p.commission_price');
				break;
			case 7: //总销量降序
				$obj 		= $obj->orderByDesc('p.sales');
				break;
			case 8: //24小时内销量降序
				$mms 		= time() - 3600 * 24;
				$obj 		= $obj->leftJoin('tiktok_order_products as o', 'o.product_id', '=', 'p.pid')->where('addtime', '>=', $mms)->orderByDesc('p.sales');
				break;
			case 9: //2小时内销售降序
				$mms 		= time() - 3600 * 2;
				$obj 		= $obj->leftJoin('tiktok_order_products as o', 'o.product_id', '=', 'p.pid')->where('addtime', '>=', $mms)->orderByDesc('p.sales');
				break;
			default:
				$obj 		= $obj->inRandomOrder();
		}

		$total 		= $obj->count();

		$product_lists = $obj->offset(($page - 1) * $limit)->limit($limit)->get();

		$product_lists->flatMap(function($val) {
            $val->lefticon = config('currency.' . $val->currency) ?? '$';
        });

		return [
			'page'			=> $page,
			'limit'			=> $limit,
			'total_limit'	=> $total,
			'product_lists'	=> $product_lists,
		];
	}

	public function getThumbsAttribute($val)
	{
		return explode(',', $val);
	}

    public function getLeftIconAttribute()
    {
        return config('currency.' . $this->currency) ?? '$';
	}

	//产品列表
	public static function list()
	{
		$obj 		= self::select('id', 'pid as product_id', 'images as image', 'name as title', 'minprice as unit_price', 'commission as commission_ratio', 'gmv as cumulative_sales', 'commissioned as accumulated_commission', 'currency', 'is_samples');
		return $obj;
		// return self::select('id', 'pid as product_id', 'images as image', 'name as title', 'minprice as unit_price', 'commission as commission_ratio', 'gmv as cumulative_sales', 'commissioned as accumulated_commission', 'currency'->orderByDesc('gmv')->offset(($page-1)*$limit)->limit($limit);
	}

	//商品详情
	public static function detail($id)
	{
		return self::select('images as banner', 'stocks as stock', 'minprice as unit_price', 'commission_price as commission', 'commission as commission_ratio', 'sales as cumulative_sales', 'fans', 'selling_point', 'currency', 'description as content', 'is_samples', 'name as title', 'pid', 'shop_id')->find($id);
	}


	public function shop()
	{
		return $this->belongsTo(TiktokShop::class);
	}
}
