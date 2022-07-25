<div class="nllppsdf" style="display: none;">
	<form class="layui-form layui-form-pane myform" action="">
		<label class="flex v mps">
			<span class="must">佣金: </span>
			<div class="ml10 flex1 flex v">
				<input type="number" name="commission" placeholder="请填写 0-100的整数, 固定佣金设置这个值就可以" class="input flex1">
				<input type="number" name="commission_to" placeholder="0-100, 如果填写,佣金将在范围内随机." class="input flex1">
			</div>
		</label>
		<div class="flex v">
			<span>价格区间: </span>
			<div class="ml10 flex1 flex v">
				<input type="number" name="minprice" class="input flex1 checks" placeholder="价格最小值 >=">
				<input type="number" name="maxprice" class="input flex1 checks" placeholder="价格最大值 <=">
			</div>
		</div>
		<div class="flex v">
			<span>库存区间: </span>
			<div class="ml10 flex1 flex v">
				<input type="number" name="minstock" class="input flex1 checks" placeholder="库存最小值 >=">
				<input type="number" name="maxstock" class="input flex1 checks" placeholder="库存最大值 <=">
			</div>
		</div>
		<div class="flex v">
			<span>原佣金区间: </span>
			<div class="ml10 flex1 flex v">
				<input type="number" name="mincomm" class="input flex1 checks" placeholder="原佣金最小值 >=">
				<input type="number" name="maxcomm" class="input flex1 checks" placeholder="原佣金最大值 <=">
			</div>
		</div>
		<!-- <div class="flex v">
			<span>创建时间: </span>
			<div class="ml10 flex1 flex v">
				<input type="number" name="mincreat" class="input flex1 checks" placeholder="创建时间范围最小值 >=">
				<input type="number" name="maxcreat" class="input flex1 checks" placeholder="创建时间范围最大值 <=">
			</div>
		</div> -->
		<div class="flex">
			<label class="flex v flex1">
				<span>账号: </span>
				<select name="account_id" class="flex1 select checks">
					<option value="">请选择...</option>
				@foreach($accounts as $k => $item)
					<option value="{{$k}}">{{$item}}</option>
				@endforeach
				</select>
			</label>
			<label class="flex v flex1" style="margin-left: 20px;">
				<span>店铺地区: </span>
				<select name="shop_id" class="flex1 select checks">
					<option value="">请选择...</option>
				@foreach($shops as $k => $item)
					<option value="{{$k}}">{{$item}}</option>
				@endforeach
				</select>
			</label>
		</div>

		<div class="flex">
			<label class="flex v flex1">
				<span>产品编号: </span>
				<input type="text" name="product_id" class="input flex1 checks" placeholder="填写产品编号,数字">
			</label>
			<label class="flex v flex1" style="margin-left: 20px;">
				<span>产品名称: </span>
				<input type="text" name="product_name" class="input flex1 checks" placeholder="产品名称将进行搜索.">
			</label>
		</div>
	</form>
</div>