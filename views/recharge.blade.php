@extends('hanoivip::layouts.app')

@section('title', 'Mua vàng trong game.')

@push('scripts')
    <script src="/js/recharge.js"></script>
@endpush

@section('content')

<div class="wrapper__post-event">
	<div class="wrapper">


<main style="color: white; font-size: 20px;">

@if (!empty($balances) && !$balances->isEmpty())
    <p>Tài khoản:</p>
    @foreach ($balances as $bal)
    	@if ($bal->type == 0)
        <div>
        	<p>Số dư: <strong id="recharge-balance">{{$bal->balance}}</strong>đ 
        	<a data-action="{{ route('api.balance.info') }}" id="recharge-balance-refresh">Cập nhật</a> </p>
        </div>
        @endif
    @endforeach
@else
	<p>Bạn vẫn chưa có xu nào!</p>
@endif

<p style="color: #337ab7;background: #f2dede;border-color: #ebccd1;text-align: center;width: 300px; margin: auto;" id="message"></p>

<p style="color: red;background: #f2dede;border-color: #ebccd1;text-align: center;width: 300px; margin: auto;" id="error-message"></p>

<style type="text/css"> 
form{
		margin: auto;
		width:  300px;
		height: 300px;
		padding-top: 20px;

	}
	form li{		
		width:  100%;
		height: 36px;
		margin-bottom: 10px;

	}
	form li label, form li input, form li select {
		float:left;
		line-height: 36px;
		padding:0 5px;
		font-size: 15px;
	}
	form li input, form li select{
		background-color: #fff;
		font-size: 12px;
		width: 131px;
		height: 30px;
		border-radius: 10px;
		float:right;
	}
	p{
		text-align: center;
	}
	form li span{
		width: 100%;
		float: right;
		line-height: 30px;
		text-align: center;
		background-color: red;
		margin-bottom: 10px;
	}
.btn_topup, .btn_recharge{
	width: 131px; border-radius: 10px;  padding: 5px 0; text-decoration: none; color: white;  height: 30px; background: #F2385A; position: relative; font-size: 14px; border-radius: 10px; transition: all 0.2s; background-color: #3498DB;  box-shadow: 0px 5px 0px 0px #258cd1;float: right;
}
</style>


<form data-action="{{ route('api.game.recharge') }}" id="recharge">
	<p style="text-align: left;">Chọn máy chủ:</p>
	<select id="recharge-svname" name="svname" style="width: 100%;" data-action="{{ route('game.roles') }}">
		@foreach ($servers as $sv)
			@if (isset($selected) && $sv->name == $selected)
				<option value="{{ $sv->name }}" selected>{{ $sv->title }}</option>
			@else
				<option value="{{ $sv->name }}">{{ $sv->title }}</option>
			@endif
		@endforeach
	</select>
	<p style="text-align: left;">Chọn số tiền:</p>
	<select id="package" name="package" style="width: 100%;">
		@foreach ($packs as $p)
			<option value="{{ $p->code }}">{{ $p->title }}</option>
		@endforeach
	</select>
	<div id="recharge-roles-div">
		@include('hanoivip::recharge-roles-partial', ['roles' => $roles])
	</div>
	<a data-action="{{ route('game.roles') }}" id="recharge-refresh-roles" data-update-id="recharge-roles-div">Làm mới ds nhân vật</a>
	<br/><br/><br/>
	<button type="submit" style="width: 100%;
    padding: 7px 0;
    text-indent: 0;
    color: #fff;
    background-color: #5cb85c;
    border-color: #4cae4c;
    border-radius: 5px;" id="btn-recharge" data-request-loading="#recharge-loading">Chuyển xu</button>
    <img src="/img/loading.gif" style="display: none;" id="recharge-loading"/>
</form>

</main>

</div></div>

@endsection
