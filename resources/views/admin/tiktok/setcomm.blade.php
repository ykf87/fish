<form class="layui-form layui-form-pane myform" action="">
	<label class="flex v">
		<span>佣金: </span>
		<input type="number" name="commission" placeholder="请填写 0-100的整数, %" class="input flex1">
	</label>
	<div class="flex">
		<label class="flex v flex1">
			<span>账号: </span>
			<select name="account_id" class="flex1 select">
				<option value="">请选择...</option>
			@foreach($accounts as $k => $item)
				<option value="{{$k}}">{{$item}}</option>
			@endforeach
			</select>
		</label>
		<label class="flex v flex1" style="margin-left: 20px;">
			<span>店铺地区: </span>
			<select name="shop_id" class="flex1 select">
				<option value="">请选择...</option>
			@foreach($shops as $k => $item)
				<option value="{{$k}}">{{$item}}</option>
			@endforeach
			</select>
		</label>
	</div>
	<div class="flex v">
		<span>价格区间: </span>
		<div class="ml10 flex1 flex v">
			<input type="number" name="minprice" class="input flex1" placeholder="价格最小值">
			<input type="number" name="maxprice" class="input flex1" placeholder="价格最大值">
		</div>
	</div>
	<div class="flex v">
		<span>库存区间: </span>
		<div class="ml10 flex1 flex v">
			<input type="number" name="stock_start" class="input flex1" placeholder="库存最小值">
			<input type="number" name="stock_end" class="input flex1" placeholder="库存最大值">
		</div>
	</div>
</form>